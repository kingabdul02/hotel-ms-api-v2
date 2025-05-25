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

        Schema::create('payment_entry_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->string('transaction');
            $table->string('payment_provider');
            $table->string('channel')->nullable();
            $table->decimal('amount', 30, 10);
            $table->string('status');
            $table->dateTime('payment_date')->nullable();
            $table->text('raw_data');
            $table->string('host_trx_ref');
            $table->string('message');
            $table->foreignId('payment_entry_id')->constrained();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_entry_transactions');
    }
};
