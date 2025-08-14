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
        Schema::table('bookings', function (Blueprint $table) {
            // Using float to stay consistent with existing total_amount column.
            // Consider changing to decimal in a future refactor for currency precision.
            $table->float('paid_amount')->default(0)->after('total_amount');
            $table->float('balance')->default(0)->after('paid_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'balance']);
        });
    }
};
