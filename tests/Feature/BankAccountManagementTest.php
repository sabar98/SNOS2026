<?php

use App\Models\BankAccount;
use App\Models\User;

test('an admin can view the bank accounts page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/bank-accounts');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/BankAccounts')
        ->has('bankAccounts')
    );
});

test('an admin can create a bank account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/bank-accounts', [
        'bank_name' => 'BCA',
        'account_number' => '1234567890',
        'account_holder' => 'Panitia SNOS 2026',
    ]);

    $response->assertRedirect();

    $bank = BankAccount::where('account_number', '1234567890')->first();
    expect($bank)->not->toBeNull();
    expect($bank->bank_name)->toBe('BCA');
    expect($bank->is_active)->toBeTrue();
});

test('an admin can update a bank account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $bank = BankAccount::create([
        'bank_name' => 'BCA',
        'account_number' => '1111111111',
        'account_holder' => 'Panitia Lama',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->put("/admin/bank-accounts/{$bank->id}", [
        'bank_name' => 'BNI',
        'account_number' => '2222222222',
        'account_holder' => 'Panitia Baru',
        'is_active' => false,
    ]);

    $response->assertRedirect();
    $bank->refresh();
    expect($bank->bank_name)->toBe('BNI');
    expect($bank->account_number)->toBe('2222222222');
    expect($bank->is_active)->toBeFalse();
});

test('an admin can delete a bank account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $bank = BankAccount::create([
        'bank_name' => 'BCA',
        'account_number' => '1111111111',
        'account_holder' => 'Panitia Dihapus',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/bank-accounts/{$bank->id}");

    $response->assertRedirect();
    expect(BankAccount::find($bank->id))->toBeNull();
});

test('a non-admin cannot manage bank accounts', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $bank = BankAccount::create([
        'bank_name' => 'BCA',
        'account_number' => '1111111111',
        'account_holder' => 'Panitia Terlindungi',
        'is_active' => true,
    ]);

    $this->actingAs($participant)->get('/admin/bank-accounts')->assertForbidden();

    $this->actingAs($participant)->post('/admin/bank-accounts', [
        'bank_name' => 'BNI',
        'account_number' => '2222222222',
        'account_holder' => 'Tidak boleh',
    ])->assertForbidden();

    $this->actingAs($participant)->put("/admin/bank-accounts/{$bank->id}", [
        'bank_name' => 'BNI',
        'account_number' => '2222222222',
        'account_holder' => 'Tidak boleh',
    ])->assertForbidden();

    $this->actingAs($participant)->delete("/admin/bank-accounts/{$bank->id}")->assertForbidden();
});
