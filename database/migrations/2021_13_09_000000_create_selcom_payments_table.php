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
            $table->string('order_id');
            $table->string('transid');
            $table->string('reference');
            $table->string('result');
            $table->string('resultcode');
            $table->string('payment_status');
            $table->integer('amount');
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
