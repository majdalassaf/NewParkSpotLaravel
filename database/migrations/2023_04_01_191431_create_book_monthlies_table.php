<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookMonthliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('book_monthlies', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('slot_id');
            $table->time("startTime_book");
            $table->time("endTime_book");
            $table->boolean('expired')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('book_monthlies');
    }
}
