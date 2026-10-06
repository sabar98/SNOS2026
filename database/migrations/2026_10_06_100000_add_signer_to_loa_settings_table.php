<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loa_settings', function (Blueprint $table) {
            $table->string('signer_name')->nullable()->after('signature_path');
            $table->string('signer_title')->nullable()->after('signer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loa_settings', function (Blueprint $table) {
            $table->dropColumn(['signer_name', 'signer_title']);
        });
    }
};
