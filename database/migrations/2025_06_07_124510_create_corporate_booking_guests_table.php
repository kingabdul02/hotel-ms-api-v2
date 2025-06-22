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
        Schema::create('corporate_booking_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corporate_booking_id')->constrained();
            $table->foreignId('room_id')->constrained();
            $table->string('full_name');
            $table->string('gender')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_checked_in')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->boolean('is_checked_out')->default(false);
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corporate_booking_guests');
    }
};
