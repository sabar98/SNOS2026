<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registration_fees', function (Blueprint $table) {
            $table->id();
            $table->string('participant_type');
            $table->string('attendance_method');
            $table->unsignedInteger('amount');
            $table->timestamps();

            $table->unique(['participant_type', 'attendance_method']);
        });

        // Seed initial fees from the previous static config, applied equally to
        // both attendance methods so existing behavior is unchanged until an
        // admin adjusts them from Admin > Biaya Pendaftaran.
        $now = now();
        $rows = [];
        foreach (config('seminar.fees', []) as $participantType => $amount) {
            foreach (['luring', 'daring'] as $attendanceMethod) {
                $rows[] = [
                    'participant_type' => $participantType,
                    'attendance_method' => $attendanceMethod,
                    'amount' => $amount,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows !== []) {
            DB::table('registration_fees')->insert($rows);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_fees');
    }
};
