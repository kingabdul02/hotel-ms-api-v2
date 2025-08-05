<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPortalAndBookingTypeToPaymentEntries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_entries', function (Blueprint $table) {
            $table->enum('portal', ['assisted', 'public'])->default('public')->after('payment_method');
            $table->enum('booking_type', ['individual', 'corporate'])->default('individual')->after('portal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_entries', function (Blueprint $table) {
            $table->dropColumn(['portal', 'booking_type']);
        });
    }
}
