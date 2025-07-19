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
        Schema::create('corporate_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('coordinator_id')->constrained();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->foreignId('meal_plan_id')->nullable()->constrained();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('reservation_code');
            $table->bigInteger('expected_guests')->default(0);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'checked_in', 'checked_out'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corporate_bookings');
    }
};
