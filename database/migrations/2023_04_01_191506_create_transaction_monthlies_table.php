<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionMonthliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_monthlies', function (Blueprint $table) {
            $table->id();
            $table->integer('bookmonthly_id');
            $table->integer('typepay_id');
            $table->double('cost');
            $table->integer('walletuser_id');
            $table->integer('walletadmin_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_monthlies');
    }
}
