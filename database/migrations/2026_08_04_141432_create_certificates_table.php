<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_registration_id')->nullable()->constrained()->nullOnDelete();
            $table->string('certificate_number')->unique();
            $table->enum('role', ['peserta', 'presenter', 'moderator', 'reviewer', 'narasumber', 'panitia']);
            $table->string('qr_code_path')->nullable();
            $table->unsignedSmallInteger('jp_hours')->nullable();
            $table->string('verification_token')->unique();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
