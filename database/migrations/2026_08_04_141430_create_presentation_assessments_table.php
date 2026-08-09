<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presentation_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presentation_slot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessed_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('mastery_score')->nullable();
            $table->unsignedTinyInteger('presentation_quality_score')->nullable();
            $table->unsignedTinyInteger('timeliness_score')->nullable();
            $table->unsignedTinyInteger('qa_score')->nullable();
            $table->unsignedTinyInteger('content_alignment_score')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presentation_assessments');
    }
};
