<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('participant_categories', function (Blueprint $table) {
            $table->string('golongan')->default('umum')->after('label');
        });

        // Backfill the one existing category that obviously maps to "mahasiswa";
        // the rest keep the "umum" default and can be adjusted from the admin page.
        DB::table('participant_categories')->where('key', 'peserta_mahasiswa')->update(['golongan' => 'mahasiswa']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participant_categories', function (Blueprint $table) {
            $table->dropColumn('golongan');
        });
    }
};
