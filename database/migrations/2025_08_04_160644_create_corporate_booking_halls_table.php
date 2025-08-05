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
        Schema::create('corporate_booking_halls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corporate_booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('hall_id')->constrained();
            $table->string('hall_name');
            $table->decimal('hall_price', 10, 2);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corporate_booking_halls');
    }
};
