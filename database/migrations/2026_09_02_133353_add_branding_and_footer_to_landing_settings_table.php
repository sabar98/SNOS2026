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
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->string('site_name')->nullable()->after('name');
            $table->string('site_logo_path')->nullable()->after('site_name');
            $table->string('organizer')->nullable()->after('site_logo_path');
            $table->json('contact')->nullable()->after('organizer');
        });

        // Backfill any row created before these columns existed, so the
        // header/footer keep showing sensible values without a re-seed.
        DB::table('landing_settings')->whereNull('site_name')->update([
            'site_name' => 'SNOS 2026',
            'organizer' => config('seminar.organizer'),
            'contact' => json_encode(config('seminar.contact')),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->dropColumn(['site_name', 'site_logo_path', 'organizer', 'contact']);
        });
    }
};
