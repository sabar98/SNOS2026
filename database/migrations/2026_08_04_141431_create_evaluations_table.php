<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_registration_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('speaker_rating')->nullable();
            $table->unsignedTinyInteger('committee_rating')->nullable();
            $table->unsignedTinyInteger('material_quality_rating')->nullable();
            $table->unsignedTinyInteger('facility_rating')->nullable();
            $table->unsignedTinyInteger('zoom_rating')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
