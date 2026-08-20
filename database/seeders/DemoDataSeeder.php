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

        // A second, fully certificate-eligible registration so "Terbitkan Sertifikat"
        // has something to select without any manual setup after a fresh seed.
        $eligibleParticipant = User::factory()->create([
            'name' => 'Peserta Selesai Contoh',
            'email' => 'peserta.selesai@snos.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'nik' => '3201234567890002',
            'institution' => 'Universitas Contoh',
            'whatsapp_number' => '081234567891',
        ]);
        $eligibleParticipant->assignRole('peserta');

        $eligibleRegistration = EventRegistration::create([
            'registration_number' => 'SNOS2026-'.Str::upper(Str::random(6)),
            'user_id' => $eligibleParticipant->id,
            'participant_type' => 'presenter_luring',
            'attendance_method' => 'luring',
            'article_scope' => 'Teknologi Informasi',
            'institution' => 'Universitas Contoh',
            'status' => 'hadir',
        ]);
        $eligibleRegistration->payments()->create([
            'type' => 'registrasi',
            'amount' => 750000,
            'payment_code' => 'PAY-DEMO-'.Str::upper(Str::random(6)),
            'status' => 'terverifikasi',
        ]);
        $eligibleRegistration->attendances()->create([
            'type' => 'registrasi_ulang',
            'method' => 'qr_code',
            'status' => 'hadir',
        ]);
        $eligibleRegistration->evaluation()->create([
            'speaker_rating' => 5,
            'committee_rating' => 5,
            'material_quality_rating' => 5,
            'facility_rating' => 5,
        ]);
        $eligibleRegistration->articles()->create([
            'title' => 'Optimasi Sistem Informasi Terpadu untuk Layanan Publik',
            'abstract' => 'Studi penerapan optimasi sistem informasi pada layanan publik berbasis kebutuhan pengguna.',
            'keywords' => 'optimasi, sistem informasi, layanan publik',
            'field' => 'Teknologi Informasi',
            'status' => 'diterima',
        ]);
        $eligibleRegistration->markCompletedIfEligible();

        $journals = collect([
            ['name' => 'Jurnal Ilmiah Teknologi Terapan', 'type' => 'jurnal', 'publisher' => 'Universitas Contoh Press'],
            ['name' => 'Prosiding Seminar Nasional SNOS', 'type' => 'prosiding', 'publisher' => 'Panitia SNOS'],
        ])->map(fn (array $journal) => Journal::create($journal + [
            'is_active' => true,
        ]));

        // Demo articles for the Publications page: one awaiting processing, one being
        // processed, and one already published, so the page has something to show.
        $publicationDemoArticles = [
            [
                'name' => 'Peserta Publikasi Satu',
                'email' => 'peserta.publikasi1@snos.test',
                'nik' => '3201234567890003',
                'title' => 'Analisis Kinerja Basis Data Terdistribusi',
                'field' => 'Teknologi Informasi',
                'journal_index' => 0,
                'publication' => null,
            ],
            [
                'name' => 'Peserta Publikasi Dua',
                'email' => 'peserta.publikasi2@snos.test',
                'nik' => '3201234567890004',
                'title' => 'Model Prediksi Permintaan Energi Menggunakan Machine Learning',
                'field' => 'Sains dan Rekayasa',
                'journal_index' => 0,
                'publication' => ['status' => 'diproses'],
            ],
            [
                'name' => 'Peserta Publikasi Tiga',
                'email' => 'peserta.publikasi3@snos.test',
                'nik' => '3201234567890005',
                'title' => 'Strategi Digitalisasi UMKM di Era Ekonomi Kreatif',
                'field' => 'Manajemen dan Ekonomi Digital',
                'journal_index' => 1,
                'publication' => [
                    'status' => 'terbit',
                    'volume' => '12',
                    'issue_number' => '3',
                    'doi' => '10.1234/snos.2026.demo',
                    'article_url' => 'https://jurnal-demo.test/artikel/strategi-digitalisasi-umkm',
                    'published_at' => now()->subDays(10),
                ],
            ],
        ];

        foreach ($publicationDemoArticles as $index => $data) {
            $participant = User::factory()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'nik' => $data['nik'],
                'institution' => 'Universitas Contoh',
                'whatsapp_number' => '0812345678'.(10 + $index),
            ]);
            $participant->assignRole('peserta');

            $registration = EventRegistration::create([
                'registration_number' => 'SNOS2026-'.Str::upper(Str::random(6)),
                'user_id' => $participant->id,
                'participant_type' => 'presenter_luring',
                'attendance_method' => 'luring',
                'article_scope' => $data['field'],
                'institution' => 'Universitas Contoh',
                'status' => 'hadir',
            ]);

            $journalId = $journals[$data['journal_index']]->id;

            $article = $registration->articles()->create([
                'title' => $data['title'],
                'abstract' => 'Artikel demo untuk menampilkan fitur pencatatan status publikasi.',
                'keywords' => 'demo, publikasi, snos',
                'field' => $data['field'],
                'status' => 'diterima',
                'journal_id' => $journalId,
            ]);

            if ($data['publication']) {
                $article->publication()->create($data['publication'] + ['journal_id' => $journalId]);
            }
        }
    }
}
