<?php

namespace Database\Seeders;

use App\Models\EventRegistration;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin SNOS',
            'email' => 'admin@snos.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        $reviewer = User::factory()->create([
            'name' => 'Dr. Reviewer Contoh',
            'email' => 'reviewer@snos.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $reviewer->assignRole('reviewer');

        $moderator = User::factory()->create([
            'name' => 'Moderator Contoh',
            'email' => 'moderator@snos.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $moderator->assignRole('moderator');

        $leadership = User::factory()->create([
            'name' => 'Pimpinan Contoh',
            'email' => 'pimpinan@snos.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $leadership->assignRole('pimpinan');

        $speaker = User::factory()->create([
            'name' => 'Narasumber Contoh',
            'email' => 'narasumber@snos.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $speaker->assignRole('narasumber');

        $participant = User::factory()->create([
            'name' => 'Peserta Contoh',
            'email' => 'peserta@snos.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'nik' => '3201234567890001',
            'institution' => 'Universitas Contoh',
            'whatsapp_number' => '081234567890',
        ]);
        $participant->assignRole('peserta');

        EventRegistration::create([
            'registration_number' => 'SNOS2026-'.Str::upper(Str::random(6)),
            'user_id' => $participant->id,
            'participant_type' => 'presenter_luring',
            'attendance_method' => 'luring',
            'article_scope' => 'Teknologi Informasi',
            'institution' => 'Universitas Contoh',
            'status' => 'draft',
        ]);

        collect([
            ['name' => 'Jurnal Ilmiah Teknologi Terapan', 'type' => 'jurnal', 'publisher' => 'Universitas Contoh Press'],
            ['name' => 'Prosiding Seminar Nasional SNOS', 'type' => 'prosiding', 'publisher' => 'Panitia SNOS'],
        ])->each(fn (array $journal) => Journal::create($journal + [
            'is_active' => true,
        ]));
    }
}
