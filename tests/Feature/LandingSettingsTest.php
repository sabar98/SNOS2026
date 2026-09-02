<?php

use App\Models\LandingSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function baseLandingPayload(): array
{
    return [
        'site_name' => 'SNOS 2026',
        'organizer' => 'Universitas Contoh',
        'contact' => [
            'email' => 'snos2026@contoh.ac.id',
            'phone' => '+62 812-3456-7890',
            'facebook' => 'https://facebook.com/snos2026',
            'instagram' => 'https://instagram.com/snos2026',
            'address' => 'Gedung Auditorium Universitas Contoh',
        ],
    ];
}

test('the landing page renders using the current landing settings, seeded from config on first use', function () {
    expect(LandingSetting::count())->toBe(0);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Landing')
        ->where('seminar.name', config('seminar.name'))
        ->where('seminar.theme', config('seminar.theme'))
        ->where('seminar.site_name', 'SNOS 2026')
    );
    expect(LandingSetting::count())->toBe(1);
});

test('an admin can view the landing settings edit page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/landing-settings');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/LandingSettings')
        ->has('setting.scope')
        ->has('setting.speakers')
        ->has('setting.timeline')
        ->has('setting.leader_message')
        ->has('setting.partners')
        ->has('setting.site_name')
        ->has('setting.organizer')
        ->has('setting.contact')
    );
});

test('an admin can update the landing settings and the change reflects on the public landing page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->put('/admin/landing-settings', baseLandingPayload() + [
        'name' => 'Seminar Uji Coba 2027',
        'theme' => 'Tema Baru',
        'date_range' => '1-2 Januari 2027',
        'location' => 'Gedung Uji Coba',
        'scope' => ['Kecerdasan Buatan', 'Rekayasa Perangkat Lunak'],
        'speakers' => [
            ['name' => 'Dr. Uji Coba', 'title' => 'Peneliti Utama'],
        ],
        'timeline' => [
            ['label' => 'Pembukaan', 'date' => '1 Januari 2027'],
        ],
        'leader_message' => [
            'name' => 'Prof. Dr. Ketua Uji Coba',
            'title' => 'Ketua Panitia',
            'message' => 'Selamat datang di kegiatan kami.',
        ],
        'partners' => [
            ['name' => 'Mitra Uji Coba'],
        ],
    ]);

    $response->assertRedirect();

    $setting = LandingSetting::current();
    expect($setting->name)->toBe('Seminar Uji Coba 2027');
    expect($setting->scope)->toBe(['Kecerdasan Buatan', 'Rekayasa Perangkat Lunak']);
    expect($setting->speakers[0])->toMatchArray(['name' => 'Dr. Uji Coba', 'title' => 'Peneliti Utama']);
    expect($setting->timeline[0])->toMatchArray(['label' => 'Pembukaan', 'date' => '1 Januari 2027']);
    expect($setting->leader_message)->toMatchArray([
        'name' => 'Prof. Dr. Ketua Uji Coba',
        'title' => 'Ketua Panitia',
        'message' => 'Selamat datang di kegiatan kami.',
    ]);
    expect($setting->partners[0])->toMatchArray(['name' => 'Mitra Uji Coba']);

    $landingResponse = $this->get('/');
    $landingResponse->assertInertia(fn ($page) => $page
        ->where('seminar.name', 'Seminar Uji Coba 2027')
        ->where('seminar.location', 'Gedung Uji Coba')
        ->where('seminar.leader_message.name', 'Prof. Dr. Ketua Uji Coba')
    );
});

test('an admin can update the site logo, name, and footer contact info, reflected on the landing page', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/landing-settings', [
        '_method' => 'put',
        'name' => 'Seminar Uji Coba',
        'site_name' => 'Nama Website Baru',
        'site_logo' => UploadedFile::fake()->image('logo.png', 200, 200),
        'organizer' => 'Yayasan Uji Coba',
        'contact' => [
            'email' => 'kontak@ujicoba.test',
            'phone' => '+62 811-0000-0000',
            'facebook' => 'https://facebook.com/ujicoba',
            'instagram' => 'https://instagram.com/ujicoba',
            'address' => 'Jl. Uji Coba No. 1',
        ],
        'theme' => 'Tema',
        'date_range' => '1 Januari 2027',
        'location' => 'Gedung Uji Coba',
        'scope' => ['Kecerdasan Buatan'],
        'speakers' => [],
        'timeline' => [],
        'leader_message' => ['name' => 'Ketua', 'title' => 'Ketua Panitia', 'message' => 'Sambutan.'],
        'partners' => [],
    ]);

    $response->assertRedirect();

    $setting = LandingSetting::current();
    expect($setting->site_name)->toBe('Nama Website Baru');
    expect($setting->site_logo_path)->not->toBeNull();
    expect($setting->organizer)->toBe('Yayasan Uji Coba');
    expect($setting->contact['email'])->toBe('kontak@ujicoba.test');
    Storage::disk('public')->assertExists($setting->site_logo_path);

    $landingResponse = $this->get('/');
    $landingResponse->assertInertia(fn ($page) => $page
        ->where('seminar.site_name', 'Nama Website Baru')
        ->where('seminar.site_logo_path', $setting->site_logo_path)
        ->where('seminar.organizer', 'Yayasan Uji Coba')
        ->where('seminar.contact.email', 'kontak@ujicoba.test')
    );
});

test('an admin can upload a speaker photo and set a topic', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/landing-settings', baseLandingPayload() + [
        '_method' => 'put',
        'name' => 'Seminar Uji Coba',
        'theme' => 'Tema',
        'date_range' => '1 Januari 2027',
        'location' => 'Gedung Uji Coba',
        'scope' => ['Kecerdasan Buatan'],
        'speakers' => [
            [
                'name' => 'Dr. Uji Foto',
                'title' => 'Peneliti Utama',
                'topic' => 'Optimasi Sistem Cerdas',
                'photo' => UploadedFile::fake()->image('speaker.jpg', 400, 400),
            ],
        ],
        'timeline' => [],
        'leader_message' => ['name' => 'Ketua', 'title' => 'Ketua Panitia', 'message' => 'Sambutan.'],
        'partners' => [],
    ]);

    $response->assertRedirect();

    $setting = LandingSetting::current();
    $speaker = $setting->speakers[0];
    expect($speaker['name'])->toBe('Dr. Uji Foto');
    expect($speaker['topic'])->toBe('Optimasi Sistem Cerdas');
    expect($speaker['photo_path'])->not->toBeNull();
    Storage::disk('public')->assertExists($speaker['photo_path']);
});

test('an admin can upload a photo for the leader welcome message', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/landing-settings', baseLandingPayload() + [
        '_method' => 'put',
        'name' => 'Seminar Uji Coba',
        'theme' => 'Tema',
        'date_range' => '1 Januari 2027',
        'location' => 'Gedung Uji Coba',
        'scope' => ['Kecerdasan Buatan'],
        'speakers' => [],
        'timeline' => [],
        'leader_message' => [
            'name' => 'Prof. Dr. Ketua Panitia',
            'title' => 'Ketua Panitia',
            'message' => 'Selamat datang di kegiatan kami.',
            'photo' => UploadedFile::fake()->image('leader.jpg', 400, 400),
        ],
        'partners' => [],
    ]);

    $response->assertRedirect();

    $leaderMessage = LandingSetting::current()->leader_message;
    expect($leaderMessage['name'])->toBe('Prof. Dr. Ketua Panitia');
    expect($leaderMessage['photo_path'])->not->toBeNull();
    Storage::disk('public')->assertExists($leaderMessage['photo_path']);
});

test('an admin can upload a partner logo', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/landing-settings', baseLandingPayload() + [
        '_method' => 'put',
        'name' => 'Seminar Uji Coba',
        'theme' => 'Tema',
        'date_range' => '1 Januari 2027',
        'location' => 'Gedung Uji Coba',
        'scope' => ['Kecerdasan Buatan'],
        'speakers' => [],
        'timeline' => [],
        'leader_message' => ['name' => 'Ketua', 'title' => 'Ketua Panitia', 'message' => 'Sambutan.'],
        'partners' => [
            [
                'name' => 'Universitas Uji Coba',
                'logo' => UploadedFile::fake()->image('partner.jpg', 200, 200),
            ],
        ],
    ]);

    $response->assertRedirect();

    $partner = LandingSetting::current()->partners[0];
    expect($partner['name'])->toBe('Universitas Uji Coba');
    expect($partner['logo_path'])->not->toBeNull();
    Storage::disk('public')->assertExists($partner['logo_path']);
});

test('re-uploading a speaker photo replaces the old file and keeps the previous photo when no new file is sent', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->post('/admin/landing-settings', baseLandingPayload() + [
        '_method' => 'put',
        'name' => 'Seminar Uji Coba',
        'theme' => 'Tema',
        'date_range' => '1 Januari 2027',
        'location' => 'Gedung Uji Coba',
        'scope' => ['Kecerdasan Buatan'],
        'speakers' => [
            ['name' => 'Dr. Uji Foto', 'title' => 'Peneliti Utama', 'photo' => UploadedFile::fake()->image('first.jpg', 400, 400)],
        ],
        'timeline' => [],
        'leader_message' => ['name' => 'Ketua', 'title' => 'Ketua Panitia', 'message' => 'Sambutan.'],
        'partners' => [],
    ]);
    $firstPath = LandingSetting::current()->speakers[0]['photo_path'];

    // Resubmit without a new file, carrying forward the existing photo_path — the photo must survive.
    $this->actingAs($admin)->put('/admin/landing-settings', baseLandingPayload() + [
        'name' => 'Seminar Uji Coba',
        'theme' => 'Tema',
        'date_range' => '1 Januari 2027',
        'location' => 'Gedung Uji Coba',
        'scope' => ['Kecerdasan Buatan'],
        'speakers' => [
            ['name' => 'Dr. Uji Foto', 'title' => 'Peneliti Utama', 'photo_path' => $firstPath],
        ],
        'timeline' => [],
        'leader_message' => ['name' => 'Ketua', 'title' => 'Ketua Panitia', 'message' => 'Sambutan.'],
        'partners' => [],
    ]);
    expect(LandingSetting::current()->speakers[0]['photo_path'])->toBe($firstPath);
    Storage::disk('public')->assertExists($firstPath);

    // Now replace it with a new file — the old file should be deleted.
    $this->actingAs($admin)->post('/admin/landing-settings', baseLandingPayload() + [
        '_method' => 'put',
        'name' => 'Seminar Uji Coba',
        'theme' => 'Tema',
        'date_range' => '1 Januari 2027',
        'location' => 'Gedung Uji Coba',
        'scope' => ['Kecerdasan Buatan'],
        'speakers' => [
            [
                'name' => 'Dr. Uji Foto',
                'title' => 'Peneliti Utama',
                'photo_path' => $firstPath,
                'photo' => UploadedFile::fake()->image('second.jpg', 400, 400),
            ],
        ],
        'leader_message' => ['name' => 'Ketua', 'title' => 'Ketua Panitia', 'message' => 'Sambutan.'],
        'timeline' => [],
        'partners' => [],
    ]);

    $secondPath = LandingSetting::current()->speakers[0]['photo_path'];
    expect($secondPath)->not->toBe($firstPath);
    Storage::disk('public')->assertExists($secondPath);
    Storage::disk('public')->assertMissing($firstPath);
});

test('updating landing settings requires at least one article scope', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->put('/admin/landing-settings', baseLandingPayload() + [
        'name' => 'Seminar Uji Coba',
        'theme' => 'Tema',
        'date_range' => '1 Januari 2027',
        'location' => 'Gedung Uji Coba',
        'scope' => [],
        'speakers' => [],
        'timeline' => [],
        'leader_message' => ['name' => 'Ketua', 'title' => 'Ketua Panitia', 'message' => 'Sambutan.'],
        'partners' => [],
    ]);

    $response->assertSessionHasErrors('scope');
});

test('a non-admin cannot view or update landing settings', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $this->actingAs($participant)->get('/admin/landing-settings')->assertForbidden();

    $this->actingAs($participant)->put('/admin/landing-settings', [
        'name' => 'Tidak boleh',
        'theme' => 'Tidak boleh',
        'date_range' => '1 Januari 2027',
        'location' => 'Tidak boleh',
        'scope' => ['Tidak boleh'],
        'speakers' => [],
        'timeline' => [],
    ])->assertForbidden();
});
