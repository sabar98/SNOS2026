<?php

use App\Models\Article;
use App\Models\EventRegistration;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

function makePresenterRegistration(?User $user = null, bool $paymentVerified = true): EventRegistration
{
    $user ??= User::factory()->create();
    $user->assignRole('peserta');

    $registration = EventRegistration::factory()->for($user, 'user')->create([
        'participant_type' => 'presenter_luring',
        'status' => $paymentVerified ? 'pembayaran_terverifikasi' : 'menunggu_verifikasi',
    ]);

    if ($paymentVerified) {
        $registration->payments()->create([
            'type' => 'registrasi',
            'amount' => 750000,
            'payment_code' => 'PAY-TEST-'.$registration->id,
            'status' => 'terverifikasi',
        ]);
    }

    return $registration;
}

test('a presenter can submit an article once payment is verified', function () {
    Storage::fake('public');
    $registration = makePresenterRegistration();

    $response = $this->actingAs($registration->user)->post("/participant/registrations/{$registration->id}/articles", [
        'title' => 'Optimasi Sistem Antrian Berbasis AI',
        'abstract' => 'Penelitian ini membahas optimasi sistem antrian.',
        'keywords' => 'antrian, optimasi, AI',
        'field' => 'Teknologi Informasi',
        'file' => UploadedFile::fake()->create('artikel.pdf', 200, 'application/pdf'),
        'statement_letter' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        'authors' => [
            ['name' => $registration->user->name, 'email' => $registration->user->email, 'is_corresponding' => true],
        ],
    ]);

    $response->assertRedirect(route('participant.registrations.show', $registration));
    $article = Article::where('event_registration_id', $registration->id)->first();
    expect($article)->not->toBeNull();
    expect($article->status)->toBe('diajukan');
    expect($article->authors)->toHaveCount(1);
    $registration->refresh();
    expect($registration->status)->toBe('artikel_diajukan');
});

test('a presenter cannot submit an article after the submission deadline has passed', function () {
    Storage::fake('public');
    $registration = makePresenterRegistration();
    $this->travelTo(Carbon::parse(config('seminar.article_submission_deadline'))->addDay());

    $response = $this->actingAs($registration->user)->post("/participant/registrations/{$registration->id}/articles", [
        'title' => 'Judul',
        'abstract' => 'Abstrak',
        'keywords' => 'kata kunci',
        'field' => 'Teknologi Informasi',
        'file' => UploadedFile::fake()->create('artikel.pdf', 200, 'application/pdf'),
        'statement_letter' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        'authors' => [['name' => 'A', 'email' => 'a@example.com']],
    ]);

    $response->assertStatus(422);
    expect(Article::where('event_registration_id', $registration->id)->count())->toBe(0);

    $this->travelBack();
});

test('a presenter can submit an article even before payment is verified', function () {
    // Explicit product decision: article submission is not gated on payment
    // status — a participant may upload their article regardless of whether
    // the registration fee has been paid or verified yet.
    Storage::fake('public');
    $registration = makePresenterRegistration(paymentVerified: false);

    $response = $this->actingAs($registration->user)->post("/participant/registrations/{$registration->id}/articles", [
        'title' => 'Judul',
        'abstract' => 'Abstrak',
        'keywords' => 'kata kunci',
        'field' => 'Teknologi Informasi',
        'file' => UploadedFile::fake()->create('artikel.pdf', 200, 'application/pdf'),
        'statement_letter' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        'authors' => [['name' => 'A', 'email' => 'a@example.com']],
    ]);

    $response->assertRedirect(route('participant.registrations.show', $registration));
    expect(Article::where('event_registration_id', $registration->id)->count())->toBe(1);
});

test('the article upload page and form are accessible regardless of payment status', function () {
    $registration = makePresenterRegistration(paymentVerified: false);

    $this->actingAs($registration->user)
        ->get("/participant/registrations/{$registration->id}/articles/create")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Participant/ArticleCreate'));
});

test('an admin can see and download the article file and statement letter a participant uploaded', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $registration = makePresenterRegistration();
    $article = Article::factory()->for($registration, 'eventRegistration')->create([
        'status' => 'diajukan',
        'file_path' => 'articles/artikel-peserta.pdf',
        'statement_letter_path' => 'article-statements/surat-peserta.pdf',
    ]);

    $this->actingAs($admin)->get("/admin/articles/{$article->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('article.file_path', 'articles/artikel-peserta.pdf')
            ->where('article.statement_letter_path', 'article-statements/surat-peserta.pdf')
        );
});

test('an admin can approve an article for review', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $registration = makePresenterRegistration();
    $article = Article::factory()->for($registration, 'eventRegistration')->create(['status' => 'diajukan']);

    $response = $this->actingAs($admin)->put("/admin/articles/{$article->id}", [
        'decision' => 'proses_review',
        'similarity_score' => 8,
    ]);

    $response->assertRedirect();
    $article->refresh();
    $registration->refresh();
    expect($article->status)->toBe('proses_review');
    expect((float) $article->similarity_score)->toBe(8.0);
    expect($registration->status)->toBe('sedang_direview');
});

test('an admin can reject an article at the administration stage', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $registration = makePresenterRegistration();
    $article = Article::factory()->for($registration, 'eventRegistration')->create(['status' => 'diajukan']);

    $response = $this->actingAs($admin)->put("/admin/articles/{$article->id}", [
        'decision' => 'ditolak_administrasi',
        'admin_notes' => 'Tidak sesuai template.',
    ]);

    $response->assertRedirect();
    $article->refresh();
    expect($article->status)->toBe('ditolak_administrasi');
    expect($article->admin_notes)->toBe('Tidak sesuai template.');
});

test('an admin can search the article list by title or author name', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $author = User::factory()->create(['name' => 'Siti Marlina']);
    $registration = makePresenterRegistration($author);
    Article::factory()->for($registration, 'eventRegistration')->create(['title' => 'Optimasi Sistem Antrian']);

    $other = makePresenterRegistration();
    Article::factory()->for($other, 'eventRegistration')->create(['title' => 'Analisis Data Lainnya']);

    $this->actingAs($admin)->get('/admin/articles?search=Siti Marlina')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('articles.total', 1)
            ->where('articles.data.0.title', 'Optimasi Sistem Antrian')
        );

    $this->actingAs($admin)->get('/admin/articles?search=Optimasi')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('articles.total', 1));
});

test('an admin can filter the article list by submission date range', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $inRange = makePresenterRegistration();
    $article1 = Article::factory()->for($inRange, 'eventRegistration')->create();
    $article1->forceFill(['created_at' => '2026-10-10 10:00:00'])->save();

    $outOfRange = makePresenterRegistration();
    $article2 = Article::factory()->for($outOfRange, 'eventRegistration')->create();
    $article2->forceFill(['created_at' => '2026-09-01 10:00:00'])->save();

    $this->actingAs($admin)->get('/admin/articles?date_from=2026-10-01&date_to=2026-10-31')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('articles.total', 1)
            ->where('articles.data.0.id', $article1->id)
        );
});

test('the admin article list is paginated at 20 per page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Article::factory()->count(25)->state(function () {
        return ['event_registration_id' => makePresenterRegistration()->id];
    })->create();

    $this->actingAs($admin)->get('/admin/articles')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('articles.total', 25)
            ->has('articles.data', 20)
        );
});

test('a non-admin cannot view the article list', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $this->actingAs($participant)->get('/admin/articles')->assertForbidden();
});

test('an admin can edit an article\'s details', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $journal = Journal::factory()->create(['is_active' => true]);
    $registration = makePresenterRegistration();
    $article = Article::factory()->for($registration, 'eventRegistration')->create([
        'title' => 'Judul Lama',
        'journal_id' => null,
    ]);

    $response = $this->actingAs($admin)->put("/admin/articles/{$article->id}/details", [
        'journal_id' => $journal->id,
        'title' => 'Judul Baru yang Diperbarui',
        'abstract' => 'Abstrak yang sudah direvisi oleh panitia.',
        'keywords' => 'revisi, admin, artikel',
        'field' => 'Ilmu Komputer',
    ]);

    $response->assertRedirect();
    $article->refresh();
    expect($article->title)->toBe('Judul Baru yang Diperbarui');
    expect($article->journal_id)->toBe($journal->id);
    expect($article->field)->toBe('Ilmu Komputer');
});

test('a non-admin cannot edit an article\'s details', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = makePresenterRegistration();
    $article = Article::factory()->for($registration, 'eventRegistration')->create();

    $response = $this->actingAs($participant)->put("/admin/articles/{$article->id}/details", [
        'title' => 'Mencoba mengubah',
        'abstract' => 'abstrak',
        'keywords' => 'kata kunci',
        'field' => 'bidang',
    ]);

    $response->assertForbidden();
});

test('an admin can delete an article along with its payments', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $registration = makePresenterRegistration();
    $article = Article::factory()->for($registration, 'eventRegistration')->create();
    $article->payments()->create([
        'type' => 'publikasi',
        'amount' => 150000,
        'payment_code' => 'PAY-DEL-TEST',
        'status' => 'belum_bayar',
    ]);

    $response = $this->actingAs($admin)->delete("/admin/articles/{$article->id}");

    $response->assertRedirect();
    expect(Article::find($article->id))->toBeNull();
    expect(Payment::where('payable_id', $article->id)->where('payable_type', Article::class)->count())->toBe(0);
});

test('a non-admin cannot delete an article', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = makePresenterRegistration();
    $article = Article::factory()->for($registration, 'eventRegistration')->create();

    $response = $this->actingAs($participant)->delete("/admin/articles/{$article->id}");

    $response->assertForbidden();
    expect(Article::find($article->id))->not->toBeNull();
});

test('an admin can export the article list to an Excel file with the expected columns', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $author = User::factory()->create([
        'name' => 'Peserta Ekspor Artikel',
        'institution' => 'Universitas Serambi Mekkah',
        'whatsapp_number' => '081234567890',
    ]);
    $registration = makePresenterRegistration($author);
    Article::factory()->for($registration, 'eventRegistration')->create([
        'title' => 'Judul Artikel untuk Ekspor',
        'status' => 'diterima',
    ]);

    $response = $this->actingAs($admin)->get('/admin/articles/export');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
    file_put_contents($tmpFile, $response->streamedContent());
    $sheet = IOFactory::load($tmpFile)->getActiveSheet();
    unlink($tmpFile);

    expect($sheet->getCell('B1')->getValue())->toBe('Nama Penulis');
    expect($sheet->getCell('C1')->getValue())->toBe('Instansi');
    expect($sheet->getCell('D1')->getValue())->toBe('Nomor HP/WhatsApp');
    expect($sheet->getCell('E1')->getValue())->toBe('Judul Artikel');
    expect($sheet->getCell('F1')->getValue())->toBe('Status');

    $row = $sheet->rangeToArray('A2:F2', null, true, false)[0];
    expect($row)->toBe([
        1,
        'Peserta Ekspor Artikel',
        'Universitas Serambi Mekkah',
        '081234567890',
        'Judul Artikel untuk Ekspor',
        'Diterima',
    ]);
});

test('exporting the article list to Excel honours the current search', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $match = User::factory()->create(['name' => 'Dicari Namanya Artikel']);
    Article::factory()->for(makePresenterRegistration($match), 'eventRegistration')->create();

    $other = User::factory()->create(['name' => 'Orang Lain Artikel']);
    Article::factory()->for(makePresenterRegistration($other), 'eventRegistration')->create();

    $response = $this->actingAs($admin)->get('/admin/articles/export?search=Dicari Namanya Artikel');

    $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
    file_put_contents($tmpFile, $response->streamedContent());
    $rows = IOFactory::load($tmpFile)->getActiveSheet()->toArray();
    unlink($tmpFile);

    $names = array_slice(array_column($rows, 1), 1);
    expect($names)->toBe(['Dicari Namanya Artikel']);
});

test('a non-admin cannot export the article list to Excel', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $this->actingAs($participant)->get('/admin/articles/export')->assertForbidden();
});

test('an admin can print the article list as a PDF with the expected columns', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $author = User::factory()->create(['name' => 'Peserta Cetak PDF Artikel']);
    $registration = makePresenterRegistration($author);
    Article::factory()->for($registration, 'eventRegistration')->create(['title' => 'Judul Artikel Cetak PDF']);

    $response = $this->actingAs($admin)->get('/admin/articles/export-pdf');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/pdf');
    expect($response->getContent())->toContain('%PDF-');
});

test('a non-admin cannot print the article list as a PDF', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $this->actingAs($participant)->get('/admin/articles/export-pdf')->assertForbidden();
});
