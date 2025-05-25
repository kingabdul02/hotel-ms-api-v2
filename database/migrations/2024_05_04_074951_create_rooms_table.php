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

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('room_type_id')->constrained();
            $table->foreignId('hotel_id')->default(1)->constrained();
            $table->float('price');
            $table->boolean('is_available')->default(true);
            $table->string('no_of_guests');
            $table->string('no_of_bedrooms');
            $table->boolean('has_sitting_room')->default(false);
            $table->string('no_of_beds');
            $table->string('no_of_baths');
            $table->string('check_in')->nullable();
            $table->string('check_out')->nullable();
            $table->boolean('is_feature')->default(false);
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
        Schema::dropIfExists('rooms');
    }
};
