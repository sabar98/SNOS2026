<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presentation_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('order')->default(1);
            $table->enum('status', ['dijadwalkan', 'hadir', 'selesai', 'tidak_hadir', 'dijadwalkan_ulang'])->default('dijadwalkan');
            $table->text('execution_notes')->nullable();
            $table->timestamps();

            $table->unique(['schedule_session_id', 'article_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presentation_slots');
    }
};
