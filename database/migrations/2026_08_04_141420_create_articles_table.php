<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('journal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('article_number')->nullable()->unique();
            $table->string('title');
            $table->text('abstract')->nullable();
            $table->string('keywords')->nullable();
            $table->string('field')->nullable();
            $table->string('file_path')->nullable();
            $table->string('statement_letter_path')->nullable();
            $table->decimal('similarity_score', 5, 2)->nullable();
            $table->text('admin_notes')->nullable();
            $table->enum('status', [
                'draft',
                'diajukan',
                'pemeriksaan_administrasi',
                'perlu_perbaikan_administrasi',
                'ditolak_administrasi',
                'proses_review',
                'sedang_direview',
                'revisi_minor',
                'revisi_mayor',
                'diterima',
                'ditolak',
            ])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
