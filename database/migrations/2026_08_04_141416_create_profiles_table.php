<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('title_prefix')->nullable();
            $table->string('title_suffix')->nullable();
            $table->enum('gender', ['laki_laki', 'perempuan'])->nullable();
            $table->text('address')->nullable();
            $table->string('institution')->nullable();
            $table->string('study_program')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('student_card_path')->nullable();
            $table->boolean('is_complete')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
