<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_reviewers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_anonymous')->default(true);
            $table->date('due_date')->nullable();
            $table->enum('status', ['ditugaskan', 'sedang_direview', 'selesai'])->default('ditugaskan');
            $table->timestamps();

            $table->unique(['article_id', 'reviewer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_reviewers');
    }
};
