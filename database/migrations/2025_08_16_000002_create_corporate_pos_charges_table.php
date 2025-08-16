<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('corporate_pos_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corporate_booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('outlet_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('item_id')->nullable()->constrained('outlet_items')->onDelete('set null');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->json('modifications')->nullable();
            $table->foreignId('server_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('table_number')->nullable();
            $table->string('notes')->nullable();
            $table->string('payment_status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('corporate_pos_charges');
    }
};
