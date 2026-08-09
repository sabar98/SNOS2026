<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik', 30)->nullable()->unique()->after('email');
            $table->string('institution')->nullable()->after('nik');
            $table->string('whatsapp_number', 20)->nullable()->after('institution');
            $table->timestamp('whatsapp_verified_at')->nullable()->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nik', 'institution', 'whatsapp_number', 'whatsapp_verified_at']);
        });
    }
};
