<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class CreateBookingTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {

        DB::unprepared('CREATE TRIGGER booking_trigger BEFORE DELETE ON bookings FOR EACH ROW
        BEGIN
        DECLARE total_cost DECIMAL(10,2);
        SELECT SUM(cost) INTO total_cost FROM transactions WHERE book_id = OLD.id;
        IF total_cost IS NULL THEN
        SET total_cost = 0;
        END IF;
        INSERT INTO book_histories (id,country,num_car,slot_id,date,hours,startTime_book,endTime_book,violation,previous,extends,merge,total_cost)
        VALUES(OLD.id,OLD.country,OLD.num_car,OLD.slot_id,OLD.date,OLD.hours,OLD.startTime_book,OLD.endTime_book,OLD.violation,OLD.previous,OLD.extends,OLD.merge,total_cost);
        END');
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS booking_trigger');
    }

}
