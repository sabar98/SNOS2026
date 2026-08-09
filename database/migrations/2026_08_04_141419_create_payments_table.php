<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->morphs('payable');
            $table->enum('type', ['registrasi', 'publikasi']);
            $table->decimal('amount', 12, 2);
            $table->string('bank_account')->nullable();
            $table->string('payment_code')->unique();
            $table->timestamp('due_at')->nullable();
            $table->string('proof_file_path')->nullable();
            $table->enum('status', [
                'belum_bayar',
                'menunggu_verifikasi',
                'terverifikasi',
                'perlu_perbaikan',
            ])->default('belum_bayar');
            $table->text('notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
