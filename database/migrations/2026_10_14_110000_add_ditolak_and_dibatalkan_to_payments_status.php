<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Widens the payments.status enum so admin can reject ("ditolak") or
     * cancel ("dibatalkan") a payment during verification, on top of the
     * existing accept / needs-correction decisions. Adding enum values is
     * non-destructive — no existing row can hold a value that is removed.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY status ENUM(
            'belum_bayar',
            'menunggu_verifikasi',
            'terverifikasi',
            'perlu_perbaikan',
            'ditolak',
            'dibatalkan'
        ) NOT NULL DEFAULT 'belum_bayar'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY status ENUM(
            'belum_bayar',
            'menunggu_verifikasi',
            'terverifikasi',
            'perlu_perbaikan'
        ) NOT NULL DEFAULT 'belum_bayar'");
    }
};
