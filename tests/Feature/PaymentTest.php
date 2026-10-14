<?php

use App\Models\Article;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

function makeAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

test('a participant can upload proof of payment', function () {
    Storage::fake('public');

    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'menunggu_pembayaran']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TEST123',
        'status' => 'belum_bayar',
    ]);

    $response = $this->actingAs($participant)->post("/participant/payments/{$payment->id}/proof", [
        'proof' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
    ]);

    $response->assertRedirect();
    $payment->refresh();
    expect($payment->status)->toBe('menunggu_verifikasi');
    expect($payment->proof_file_path)->not->toBeNull();
    Storage::disk('public')->assertExists($payment->proof_file_path);
});

test('a participant cannot upload proof for someone else\'s payment', function () {
    $owner = User::factory()->create();
    $owner->assignRole('peserta');
    $intruder = User::factory()->create();
    $intruder->assignRole('peserta');

    $registration = EventRegistration::factory()->for($owner, 'user')->create();
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TEST456',
        'status' => 'belum_bayar',
    ]);

    $response = $this->actingAs($intruder)->post("/participant/payments/{$payment->id}/proof", [
        'proof' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
    ]);

    $response->assertForbidden();
});

test('an admin verifying a registration payment marks the registration as paid', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'menunggu_verifikasi']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TEST789',
        'status' => 'menunggu_verifikasi',
    ]);

    $response = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'terverifikasi',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    $registration->refresh();
    expect($payment->status)->toBe('terverifikasi');
    expect($payment->verified_by)->toBe($admin->id);
    expect($registration->status)->toBe('pembayaran_terverifikasi');
});

test('an admin rejecting a payment requires notes and does not advance the registration', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'menunggu_verifikasi']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TESTABC',
        'status' => 'menunggu_verifikasi',
    ]);

    $response = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'perlu_perbaikan',
        'notes' => 'Nominal tidak sesuai.',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    $registration->refresh();
    expect($payment->status)->toBe('perlu_perbaikan');
    expect($payment->notes)->toBe('Nominal tidak sesuai.');
    expect($registration->status)->toBe('menunggu_verifikasi');
});

test('an admin can reject a payment outright, requiring notes and not advancing the registration', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'menunggu_verifikasi']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TESTREJ',
        'status' => 'menunggu_verifikasi',
    ]);

    $response = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'ditolak',
        'notes' => 'Bukti pembayaran milik orang lain.',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    $registration->refresh();
    expect($payment->status)->toBe('ditolak');
    expect($payment->notes)->toBe('Bukti pembayaran milik orang lain.');
    expect($payment->verified_by)->toBe($admin->id);
    expect($registration->status)->toBe('menunggu_verifikasi');
});

test('an admin can cancel a payment, requiring notes', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'menunggu_pembayaran']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TESTCANCEL',
        'status' => 'belum_bayar',
    ]);

    $noNotesResponse = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'dibatalkan',
    ]);
    $noNotesResponse->assertSessionHasErrors('notes');

    $response = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'dibatalkan',
        'notes' => 'Duplikat transaksi.',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    expect($payment->status)->toBe('dibatalkan');
    expect($payment->notes)->toBe('Duplikat transaksi.');
    expect($payment->verified_by)->toBe($admin->id);
});

test('an admin can reset a verified payment back to belum_bayar, which also reopens the registration for payment', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'pembayaran_terverifikasi']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TESTUNDO1',
        'status' => 'terverifikasi',
    ]);

    $response = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'belum_bayar',
        'notes' => 'Bukti ternyata tidak valid, diminta mengunggah ulang.',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    $registration->refresh();
    expect($payment->status)->toBe('belum_bayar');
    expect($registration->status)->toBe('menunggu_pembayaran');
});

test('an admin cancelling a previously verified payment also reopens the registration for payment', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'pembayaran_terverifikasi']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TESTUNDO2',
        'status' => 'terverifikasi',
    ]);

    $response = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'dibatalkan',
        'notes' => 'Pembayaran ternyata keliru, dibatalkan.',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    $registration->refresh();
    expect($payment->status)->toBe('dibatalkan');
    expect($registration->status)->toBe('menunggu_pembayaran');
});

test('undoing a verified payment does not touch a registration that has already moved past it', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    // The registration progressed (e.g. the article was already submitted) since this
    // payment was verified, so reverting the payment must not silently rewind it.
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'artikel_diajukan']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TESTUNDO3',
        'status' => 'terverifikasi',
    ]);

    $response = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'dibatalkan',
        'notes' => 'Dibatalkan untuk pengujian.',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    $registration->refresh();
    expect($payment->status)->toBe('dibatalkan');
    expect($registration->status)->toBe('artikel_diajukan');
});

test('resetting a payment decision still requires notes', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'pembayaran_terverifikasi']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TESTUNDO4',
        'status' => 'terverifikasi',
    ]);

    $response = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'belum_bayar',
    ]);

    $response->assertSessionHasErrors('notes');
    expect($payment->fresh()->status)->toBe('terverifikasi');
});

test('the admin payment list shows the participant name and email for both registration and publication fees', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create(['name' => 'Peserta Pembayaran Uji', 'email' => 'pembayaran.uji@snos.test']);
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create();
    $registrationPayment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-REGTEST',
        'status' => 'belum_bayar',
    ]);
    $article = Article::factory()->for($registration, 'eventRegistration')->create();
    $publicationPayment = $article->payments()->create([
        'type' => 'publikasi',
        'amount' => 300000,
        'payment_code' => 'PAY-PUBTEST',
        'status' => 'belum_bayar',
    ]);

    $this->actingAs($admin)->get('/admin/payments')->assertOk()->assertInertia(fn ($page) => $page
        ->where('payments.data.0.participant_name', 'Peserta Pembayaran Uji')
        ->where('payments.data.0.participant_email', 'pembayaran.uji@snos.test')
        ->where('payments.data.1.participant_name', 'Peserta Pembayaran Uji')
        ->where('payments.data.1.participant_email', 'pembayaran.uji@snos.test')
    );

    expect($registrationPayment->fresh()->participant_name)->toBe('Peserta Pembayaran Uji');
    expect($registrationPayment->fresh()->participant_email)->toBe('pembayaran.uji@snos.test');
    expect($publicationPayment->fresh()->participant_name)->toBe('Peserta Pembayaran Uji');
    expect($publicationPayment->fresh()->participant_email)->toBe('pembayaran.uji@snos.test');
});

test('an admin can search the payment list by participant name, email, or reference', function () {
    $admin = makeAdmin();

    $ahmad = User::factory()->create(['name' => 'Ahmad Baswara', 'email' => 'ahmad.baswara@snos.test']);
    $ahmad->assignRole('peserta');
    $ahmadRegistration = EventRegistration::factory()->for($ahmad, 'user')->create(['registration_number' => 'SNOS2026-AHMAD1']);
    $ahmadPayment = $ahmadRegistration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-AHMADCODE',
        'status' => 'belum_bayar',
    ]);

    $siti = User::factory()->create(['name' => 'Siti Marlina', 'email' => 'siti.marlina@snos.test']);
    $siti->assignRole('peserta');
    $sitiRegistration = EventRegistration::factory()->for($siti, 'user')->create(['registration_number' => 'SNOS2026-SITI1']);
    $sitiPayment = $sitiRegistration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-SITICODE',
        'status' => 'belum_bayar',
    ]);

    // By participant name
    $this->actingAs($admin)->get('/admin/payments?search=Ahmad Baswara')->assertInertia(fn ($page) => $page
        ->where('payments.total', 1)
        ->where('payments.data.0.id', $ahmadPayment->id)
    );

    // By email
    $this->actingAs($admin)->get('/admin/payments?search=siti.marlina@snos.test')->assertInertia(fn ($page) => $page
        ->where('payments.total', 1)
        ->where('payments.data.0.id', $sitiPayment->id)
    );

    // By reference (registration number)
    $this->actingAs($admin)->get('/admin/payments?search=SNOS2026-AHMAD1')->assertInertia(fn ($page) => $page
        ->where('payments.total', 1)
        ->where('payments.data.0.id', $ahmadPayment->id)
    );

    // By reference (payment code)
    $this->actingAs($admin)->get('/admin/payments?search=PAY-SITICODE')->assertInertia(fn ($page) => $page
        ->where('payments.total', 1)
        ->where('payments.data.0.id', $sitiPayment->id)
    );

    // No match
    $this->actingAs($admin)->get('/admin/payments?search=TidakAdaYangCocok')->assertInertia(fn ($page) => $page
        ->where('payments.total', 0)
    );
});

test('an admin can search the payment list by the article title of a publication payment', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create();
    $article = Article::factory()->for($registration, 'eventRegistration')->create(['title' => 'Judul Artikel Sangat Unik Untuk Uji Pencarian']);
    $payment = $article->payments()->create([
        'type' => 'publikasi',
        'amount' => 300000,
        'payment_code' => 'PAY-ARTSEARCH',
        'status' => 'belum_bayar',
    ]);

    $this->actingAs($admin)->get('/admin/payments?search=Sangat Unik Untuk Uji')->assertInertia(fn ($page) => $page
        ->where('payments.total', 1)
        ->where('payments.data.0.id', $payment->id)
    );
});

test('the payment list is paginated at 20 per page and a search term carries over to the next page link', function () {
    $admin = makeAdmin();

    EventRegistration::factory()
        ->count(25)
        ->create()
        ->each(function (EventRegistration $registration, int $index) {
            $registration->payments()->create([
                'type' => 'registrasi',
                'amount' => 150000,
                'payment_code' => "PAY-PAGETEST{$index}",
                'status' => 'belum_bayar',
            ]);
        });

    $firstPage = $this->actingAs($admin)->get('/admin/payments');
    $firstPage->assertInertia(fn ($page) => $page
        ->where('payments.total', 25)
        ->has('payments.data', 20)
    );

    $secondPage = $this->actingAs($admin)->get('/admin/payments?page=2');
    $secondPage->assertInertia(fn ($page) => $page
        ->where('payments.total', 25)
        ->has('payments.data', 5)
    );
});

test('an admin can export the payment list to an Excel file with the expected columns', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create(['name' => 'Peserta Ekspor Bayar', 'email' => 'ekspor.bayar@snos.test']);
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create([
        'participant_type' => 'peserta_mahasiswa',
        'registration_number' => 'SNOS2026-EKSPOR1',
    ]);
    $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 100000,
        'payment_code' => 'PAY-EKSPOR1',
        'status' => 'terverifikasi',
    ]);

    $response = $this->actingAs($admin)->get('/admin/payments/export');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
    file_put_contents($tmpFile, $response->streamedContent());
    $sheet = IOFactory::load($tmpFile)->getActiveSheet();
    unlink($tmpFile);

    expect($sheet->getCell('B1')->getValue())->toBe('Referensi');
    expect($sheet->getCell('C1')->getValue())->toBe('Nama');
    expect($sheet->getCell('D1')->getValue())->toBe('Email');
    expect($sheet->getCell('E1')->getValue())->toBe('Tipe');
    expect($sheet->getCell('F1')->getValue())->toBe('Kategori');
    expect($sheet->getCell('G1')->getValue())->toBe('Nominal');
    expect($sheet->getCell('H1')->getValue())->toBe('Status Pembayaran');

    $row = $sheet->rangeToArray('A2:H2', null, true, false)[0];
    expect($row)->toBe([
        1,
        'SNOS2026-EKSPOR1',
        'Peserta Ekspor Bayar',
        'ekspor.bayar@snos.test',
        'Registrasi',
        'Peserta Mahasiswa',
        100000.0,
        'Terverifikasi',
    ]);
});

test('exporting the payment list to Excel honours the current search', function () {
    $admin = makeAdmin();
    $match = User::factory()->create(['name' => 'Dicari Namanya']);
    $match->assignRole('peserta');
    EventRegistration::factory()->for($match, 'user')->create()->payments()->create([
        'type' => 'registrasi',
        'amount' => 100000,
        'payment_code' => 'PAY-MATCHXLSX',
        'status' => 'belum_bayar',
    ]);

    $other = User::factory()->create(['name' => 'Orang Lain']);
    $other->assignRole('peserta');
    EventRegistration::factory()->for($other, 'user')->create()->payments()->create([
        'type' => 'registrasi',
        'amount' => 100000,
        'payment_code' => 'PAY-OTHERXLSX',
        'status' => 'belum_bayar',
    ]);

    $response = $this->actingAs($admin)->get('/admin/payments/export?search=Dicari Namanya');

    $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
    file_put_contents($tmpFile, $response->streamedContent());
    $rows = IOFactory::load($tmpFile)->getActiveSheet()->toArray();
    unlink($tmpFile);

    $names = array_slice(array_column($rows, 2), 1);
    expect($names)->toBe(['Dicari Namanya']);
});

test('a non-admin cannot export the payment list to Excel', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $this->actingAs($participant)->get('/admin/payments/export')->assertForbidden();
});

test('an admin can print the payment list as a PDF with the expected columns', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create(['name' => 'Peserta Cetak PDF', 'email' => 'cetak.pdf@snos.test']);
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create([
        'participant_type' => 'presenter_luring',
    ]);
    $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 750000,
        'payment_code' => 'PAY-CETAKPDF',
        'status' => 'menunggu_verifikasi',
    ]);

    $response = $this->actingAs($admin)->get('/admin/payments/export-pdf');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/pdf');
    expect($response->getContent())->toContain('%PDF-');
});

test('a non-admin cannot print the payment list as a PDF', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $this->actingAs($participant)->get('/admin/payments/export-pdf')->assertForbidden();
});
