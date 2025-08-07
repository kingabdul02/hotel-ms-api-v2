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
        Schema::create('housekeeper_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housekeeper_id')->constrained('users');
            $table->foreignId('room_id')->constrained('rooms');
            $table->date('assignment_date');
            $table->string('shift')->nullable(); // e.g., morning, afternoon
            $table->string('status')->default('pending'); // e.g., pending, in_progress, completed
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housekeeper_assignments');
    }
};
