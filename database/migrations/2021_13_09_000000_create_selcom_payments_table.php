<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSelcomPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('selcom_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('amount');
            $table->string('order_id')->unique();
            $table->string('transid')->unique();
            $table->string('selcom_transaction_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('gateway_buyer_uuid')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('reference')->nullable();
            $table->string('msisdn')->nullable();
            $table->string('channel')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('selcom_payments');
    }
}
