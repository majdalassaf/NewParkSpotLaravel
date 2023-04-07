<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('country');
            $table->integer('num_car')->unsigned();
            $table->foreign(['country', 'num_car'])->references(['country', 'num_car'])->on('cars')->onDelete('cascade');
            $table->unique(['country', 'num_car']);
            $table->integer('slot_id');
            $table->date('date');
            $table->integer("hours")->unsigned();
            $table->time("startTime_book");
            $table->time("endTime_book");
            $table->boolean('violation')->default(false);
            $table->time("startTime_violation")->default(null);
            $table->boolean('previous')->default(false);
            $table->boolean('extends')->default(false);
            $table->boolean('merge')->default(false);
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
        Schema::dropIfExists('bookings');
    }
}
