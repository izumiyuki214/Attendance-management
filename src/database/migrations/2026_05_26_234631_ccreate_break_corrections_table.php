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
        Schema::create('break_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_correction_id')->constrained('attendance_corrections');
            $table->dateTime('break_start');
            $table->dateTime('break_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('break_corrections');
    }
};