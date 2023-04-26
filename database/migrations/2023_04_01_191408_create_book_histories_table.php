<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('book_histories', function (Blueprint $table) {
            $table->id();
            $table->string('country');
            $table->string('num_car');
            $table->integer('slot_id');
            $table->date('date');
            $table->integer("hours")->unsigned();
            $table->time("startTime_book");
            $table->time("endTime_book");
            $table->boolean('violation')->default(false);
            $table->boolean('previous')->default(false);
            $table->boolean('extends')->default(false);
            $table->boolean('merge')->default(false);
            $table->double('total_cost');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('book_histories');
    }
}
