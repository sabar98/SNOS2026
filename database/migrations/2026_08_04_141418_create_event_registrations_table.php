<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('participant_type', [
                'presenter_luring',
                'presenter_daring',
                'peserta_umum',
                'peserta_mahasiswa',
            ]);
            $table->enum('attendance_method', ['luring', 'daring']);
            $table->string('article_scope')->nullable();
            $table->string('institution')->nullable();
            $table->text('special_needs')->nullable();
            $table->boolean('join_gala_dinner')->default(false);
            $table->timestamp('terms_accepted_at')->nullable();
            $table->enum('status', [
                'draft',
                'menunggu_pembayaran',
                'menunggu_verifikasi',
                'pembayaran_terverifikasi',
                'artikel_diajukan',
                'sedang_direview',
                'perlu_revisi',
                'artikel_diterima',
                'jadwal_ditetapkan',
                'siap_mengikuti_seminar',
                'hadir',
                'selesai',
                'sertifikat_terbit',
                'ditolak',
            ])->default('draft');
            $table->timestamp('payment_due_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
