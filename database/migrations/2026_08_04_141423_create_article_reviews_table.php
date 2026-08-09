<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_reviewer_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('theme_suitability_score')->nullable();
            $table->unsignedTinyInteger('novelty_score')->nullable();
            $table->unsignedTinyInteger('methodology_score')->nullable();
            $table->unsignedTinyInteger('results_discussion_score')->nullable();
            $table->unsignedTinyInteger('reference_quality_score')->nullable();
            $table->unsignedTinyInteger('language_grammar_score')->nullable();
            $table->text('comments')->nullable();
            $table->enum('recommendation', [
                'diterima_tanpa_revisi',
                'diterima_revisi_minor',
                'revisi_mayor',
                'ditolak',
            ]);
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_reviews');
    }
};
