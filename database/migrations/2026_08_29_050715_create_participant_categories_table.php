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
        Schema::create('participant_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->boolean('is_presenter')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed the four categories the app previously hardcoded, so existing
        // registrations, fee rules, and forms keep working unchanged.
        $now = now();
        DB::table('participant_categories')->insert([
            ['key' => 'presenter_luring', 'label' => 'Presenter Luring', 'is_presenter' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'presenter_daring', 'label' => 'Presenter Daring', 'is_presenter' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'peserta_umum', 'label' => 'Peserta Umum', 'is_presenter' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'peserta_mahasiswa', 'label' => 'Peserta Mahasiswa', 'is_presenter' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_categories');
    }
};
