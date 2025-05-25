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
        Schema::disableForeignKeyConstraints();

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('room_id')->constrained();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->text('special_requests')->nullable();
            $table->float('total_amount');
            $table->enum('payment_status', ["pending","paid","cancelled", "refunded"])->default('pending');
            $table->boolean('is_confirmed')->default(true);
            $table->boolean('is_checked_in')->default(false);
            $table->boolean('is_checked_out')->default(false);
            $table->string('no_of_guests');
            $table->string('no_of_nights');
            $table->string('booking_id')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
