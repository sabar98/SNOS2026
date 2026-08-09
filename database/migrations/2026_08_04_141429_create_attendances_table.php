<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_session_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['registrasi_ulang', 'sesi']);
            $table->enum('method', ['qr_code', 'nomor_registrasi', 'tiket_digital', 'link_khusus', 'kode_zoom']);
            $table->enum('status', ['belum_hadir', 'hadir'])->default('belum_hadir');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
