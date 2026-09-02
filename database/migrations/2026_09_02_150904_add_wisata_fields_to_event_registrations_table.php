<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->boolean('join_wisata_sabang')->default(false)->after('join_gala_dinner');
            $table->boolean('join_wisata_lokal')->default(false)->after('join_wisata_sabang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn(['join_wisata_sabang', 'join_wisata_lokal']);
        });
    }
};
