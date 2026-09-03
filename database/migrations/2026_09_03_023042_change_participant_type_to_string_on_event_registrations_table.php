<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `participant_categories.key` is admin-editable (categories can be added/renamed
     * freely from Admin > Kategori Peserta), but this column was still the original
     * hardcoded ENUM('presenter_luring','presenter_daring','peserta_umum','peserta_mahasiswa').
     * Registering under any newer category (e.g. a "Peserta Dosen" golongan) made MySQL
     * reject/truncate the value and the whole registration failed with a 500. Widening
     * to a plain string brings this column in line with `registration_fees.participant_type`,
     * which was already a plain string from day one.
     *
     * Uses raw SQL instead of Schema::table()->change() since doctrine/dbal isn't installed.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE event_registrations MODIFY participant_type VARCHAR(255) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE event_registrations MODIFY participant_type ENUM('presenter_luring','presenter_daring','peserta_umum','peserta_mahasiswa') NOT NULL");
    }
};
