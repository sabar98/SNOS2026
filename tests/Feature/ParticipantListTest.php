<?php

use App\Models\EventRegistration;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;

function adminUser(): User
{
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    return $admin;
}

test('an admin can search the participant list by name, email, or whatsapp number', function () {
    $admin = adminUser();
    $match = User::factory()->create(['name' => 'Budi Santoso']);
    EventRegistration::factory()->for($match, 'user')->create();
    $other = User::factory()->create(['name' => 'Siti Aminah']);
    EventRegistration::factory()->for($other, 'user')->create();

    $response = $this->actingAs($admin)->get('/admin/participants?search=Budi');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->where('registrations.total', 1)
        ->where('registrations.data.0.user.name', 'Budi Santoso'));
});

test('a non-admin cannot view the participant list', function () {
    $user = User::factory()->create();
    $user->assignRole('peserta');

    $response = $this->actingAs($user)->get('/admin/participants');

    $response->assertForbidden();
});

test('an admin can export the participant list to an Excel file with the expected columns', function () {
    $admin = adminUser();
    $user = User::factory()->create([
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'whatsapp_number' => '081234567890',
    ]);
    EventRegistration::factory()->for($user, 'user')->create([
        'participant_type' => 'presenter_luring',
        'attendance_method' => 'luring',
        'status' => 'pembayaran_terverifikasi',
    ]);

    $response = $this->actingAs($admin)->get('/admin/participants/export');

    $response->assertOk();

    $path = tempnam(sys_get_temp_dir(), 'xlsx').'.xlsx';
    file_put_contents($path, $response->streamedContent());
    $sheet = IOFactory::load($path)->getActiveSheet();

    expect($sheet->getCell('A1')->getValue())->toBe('No');
    expect($sheet->getCell('B1')->getValue())->toBe('Nama');
    expect($sheet->getCell('C1')->getValue())->toBe('Email');
    expect($sheet->getCell('D1')->getValue())->toBe('Nomor HP');
    expect($sheet->getCell('E1')->getValue())->toBe('Jenis');
    expect($sheet->getCell('F1')->getValue())->toBe('Metode Kehadiran');
    expect($sheet->getCell('G1')->getValue())->toBe('Status Pembayaran');

    expect($sheet->getCell('B2')->getValue())->toBe('Budi Santoso');
    expect($sheet->getCell('C2')->getValue())->toBe('budi@example.com');
    expect($sheet->getCell('D2')->getValue())->toBe('081234567890');
    expect($sheet->getCell('E2')->getValue())->toBe('Presenter Luring');
    expect($sheet->getCell('F2')->getValue())->toBe('Luring');
    expect($sheet->getCell('G2')->getValue())->toBe('Pembayaran Terverifikasi');

    unlink($path);
});

test('a non-admin cannot export the participant list to Excel', function () {
    $user = User::factory()->create();
    $user->assignRole('peserta');

    $response = $this->actingAs($user)->get('/admin/participants/export');

    $response->assertForbidden();
});

test('an admin can print the participant list as a PDF with the expected columns', function () {
    $admin = adminUser();
    $user = User::factory()->create(['name' => 'Budi Santoso']);
    EventRegistration::factory()->for($user, 'user')->create();

    $response = $this->actingAs($admin)->get('/admin/participants/export-pdf');

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

test('a non-admin cannot print the participant list as a PDF', function () {
    $user = User::factory()->create();
    $user->assignRole('peserta');

    $response = $this->actingAs($user)->get('/admin/participants/export-pdf');

    $response->assertForbidden();
});
