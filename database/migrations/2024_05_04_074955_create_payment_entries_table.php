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

        Schema::create('payment_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained();
            $table->float('payment_amount');
            $table->enum('payment_method', ['online', 'cash', 'pos', 'transfer'])->nullable();
            $table->string('transaction_id')->unique();
            $table->enum('payment_status', ['pending', 'successful', 'failed', 'refunded'])->default('pending');
            $table->dateTime('payment_date')->nullable();
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
        Schema::dropIfExists('payment_entries');
    }
};
