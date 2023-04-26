<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Car;
use App\Models\Slot;
use App\Models\Zone;
use App\Models\Booking;
use App\Models\MergeSlot;
use App\Models\WalletUser;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\TraitApiResponse;
use App\Http\Controllers\SlotController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Wallet_UserController;

class BookController extends Controller{
use TraitApiResponse;

    public function create_book_user_previous(Request $request)
    {

        $Request_user = Auth::guard('user')->user();

        $end_shift=Carbon::now();
        $start_shift=Carbon::now();
        $end_shift->setTime(0,00);
        $time_now=Carbon::now()->setTimezone('Asia/Damascus')->subHours(10);
        $difEnd_Now=$end_shift->diffInHours($time_now);


        if ( $difEnd_Now >= 21)
        return $this->returnResponse("","You can't reserve, it's over, you can park for free",401);

        if ($difEnd_Now < 8 )
        return $this->returnResponse("","You can't book, the working time hasn't started, the time starts at 08:00 AM ",401);
        $car_user = Car::where('num_car', $request->num_car)->where('country',$request->country)->first();
        if(!$car_user)
        return $this->returnResponse("","Your car is not found",404);
        if(Booking::where('num_car', $request->num_car)->where('country',$request->country)->first())
        return $this->returnResponse("","You already have a reservation. You cannot book",400);

        $walletController = app(Wallet_UserController::class);
        $accept=$walletController-> Check_Amount($request->hours,"preivous",$Request_user->id);

        if (!$accept)
        return $this->returnResponse("","No Amount",400);


        $SlotController = app(SlotController::class);
        $slot=$SlotController-> Auto_Slot_id($request->zone_id);
        if (!$slot)
            return $this->returnResponse("","No Slots Available for This Park",400);

        $book = new Booking();
        $book->country = $car_user->country;
        $book->num_car = $car_user->num_car;
        $book->slot_id = $slot->id;
        $zone = Zone::where('id', $slot->zone_id)->first();
        $book->hours = $request->hours;
        $book->date = Carbon::now()->today()->tz('Asia/Damascus');
        $book->startTime_book = Carbon::now()->tz('Asia/Damascus');
        $book->endTime_book = Carbon::now()->tz('Asia/Damascus')->addHour(intval($request->hours));
        $book->startTime_violation = $end_shift;
        $book->previous=true;
        $result = $book->save();


        if ($result) {
            $walletController = app(Wallet_UserController::class);
            $accept=$walletController-> withdraw($request->hours,"preivous",$Request_user->id,$book->id);

            if(!$accept){
                $SlotController->slot_is_empty($slot);
                $book->delete();
                return $this->returnResponse("","Error transaction",400);
            }

            $SlotController->unlocked($slot);

        return $this->returnResponse('',"Successfully Book",201);
        }

        $SlotController->slot_is_empty($slot);

        return $this->returnResponse('',"oops..!!, You Can Not Book on This Park.",400);

    }



    public function create_book_user_now(Request $request){

        $Request_user = Auth::guard('user')->user();

        $end_shift=Carbon::now();
        $start_shift=Carbon::now();
        $end_shift->setTime(0,00);
        $time_now=Carbon::now()->setTimezone('Asia/Damascus')->subHours(10);
        $difEnd_Now=$end_shift->diffInHours($time_now);


        if ( $difEnd_Now >= 21)
        return $this->returnResponse("","You can't reserve, it's over, you can park for free",401);

        if ($difEnd_Now < 8 )
        return $this->returnResponse("","You can't book, the working time hasn't started, the time starts at 08:00 AM ",401);

        $car_user = Car::where('num_car', $request->num_car)->where('country',$request->country)->first();
        if(!$car_user)
        return $this->returnResponse("","Your car is not found",404);

        if(Booking::where('num_car', $request->num_car)->where('country',$request->country)->first())
        return $this->returnResponse("","You already have a reservation. You cannot book",400);

        $walletController = app(Wallet_UserController::class);
        $accept=$walletController-> Check_Amount($request->hours,"preivous",$Request_user->id);

        if (!$accept)
        return $this->returnResponse("","No Amount",400);


        $SlotController = app(SlotController::class);
        $slot=$SlotController-> Book_Slot_id($request->zone_id,$request->slot_id);
        if (!$slot)
            return $this->returnResponse("","No Slots Available for This Park",400);

        $book = new Booking();
        $book->country = $car_user->country;
        $book->num_car = $car_user->num_car;
        $book->slot_id = $slot->id;
        $zone = Zone::where('id', $slot->zone_id)->first();
        $book->hours = $request->hours;
        $book->date = Carbon::now()->today()->tz('Asia/Damascus');
        $book->startTime_book = Carbon::now()->tz('Asia/Damascus');
        $book->endTime_book = Carbon::now()->tz('Asia/Damascus')->addHour(intval($request->hours));
        $book->startTime_violation = $end_shift;
        $result = $book->save();



        if ($result) {
            $walletController = app(Wallet_UserController::class);
            $accept=$walletController-> withdraw($request->hours,"hourly",$Request_user->id,$book->id);

            if(!$accept){
                $SlotController->slot_is_empty($slot);
                $book->delete();
                return $this->returnResponse("","Error transaction",400);
            }


            $SlotController->unlocked($slot);


        return $this->returnResponse('',"Successfully Book",201);
        }

        $SlotController->slot_is_empty($slot);

        return $this->returnResponse('',"oops..!!, You Can Not Book on This Park.",400);

    }


    // كرمال يصير يجيب الحجز تبع اليوزر
    public function Get_Book(Request $request)
    {
        $Request_user = Auth::guard('user')->user();

        $CarController = app(Car_Controller::class);
        $check_car=$CarController-> Check_Car($request->num_car,$request->country,$Request_user->id);

        if(!$check_car)
            return $this->returnResponse("","Verify vehicle ownership",404);

        $book= Booking::where('num_car', $request->num_car)->where('country',$request->country)->first();
        if(!$book)
            return $this->returnResponse("","You do not have a reservation",400);

        $current_time=Carbon::now()->tz('Asia/Damascus');
        $calc_time = $current_time->diffInSeconds($book->endTime_book);

        $slot = Slot::where('id',$book->slot_id)->first();
        $zone = Zone::where('id', $slot->zone_id)->first();
        $book->park_spot = $slot->num_slot;
        $book->zone_name = $zone->name;
        $book->calc_time=$calc_time;

        return $this->returnResponse($book,"You have a reservation",200);

    }



    public function Extend_ParkingTime(Request $request)
    {
        $Request_user = Auth::guard('user')->user();
        $walletController = app(Wallet_UserController::class);
        $accept=$walletController-> Check_Amount($request->hours,"extend",$Request_user->id);
        if (!$accept)
        return $this->returnResponse("","No Amount",400);

        $end_shift=Carbon::now();
        $start_shift=Carbon::now();
        $end_shift->setTime(0,00);
        $time_now=Carbon::now()->setTimezone('Asia/Damascus')->subHours(10);
        $difEnd_Now=$end_shift->diffInHours($time_now);


        if ( $difEnd_Now >= 21)
            return $this->returnResponse("","You can't extend the time, the work time has expired, you can park for free",401);

        $book=Booking::find($request->book_id);
        $new_end_time = Carbon::parse($book->endTime_book);
        $book->endTime_book = $new_end_time->addHour(intval($request->hours));
        $new_hours = $book->hours + $request->hours;

        $status=$book->update([
            'endTime_book'=>$new_end_time,
            'hours'=>$new_hours,
            'extends'=>true,
        ]);
        if(!$status)
            return $this->returnResponse('',"The extension has not been completed, please try again",400);

        $walletController = app(Wallet_UserController::class);
        $accept=$walletController-> withdraw($request->hours,"extend",$Request_user->id,$book->id);

        if(!$accept)
            return $this->returnResponse('',"The extension has not been completed, please try again",400);

        return $this->returnResponse('',"The time has been extended successfully",200);
    }

    public function End_Booking(Request $request)
    {
        $book= Booking::where('id', $request->book_id)->first();
        if(!$book)
            return $this->returnResponse('',"Your reservation has already expired",400);

        $status=$book->delete();
        $SlotController = app(SlotController::class);
        $slot=$SlotController-> slot_is_empty_id($book->slot_id);
        if($slot)
            return $this->returnResponse('',"Your reservation has been completed.",200);

            return $this->returnResponse('',"Try again, thanks",400);

    }






    public function create_book_admin(Request $request){


        $Request_admin = Auth::guard('admin')->user();

        $end_shift=Carbon::now();
        $start_shift=Carbon::now();
        $end_shift->setTime(0,00);
        $time_now=Carbon::now()->setTimezone('Asia/Damascus')->subHours(10);
        $difEnd_Now=$end_shift->diffInHours($time_now);


        if ( $difEnd_Now >= 21)
        return $this->returnResponse("","You can't reserve, it's over, you can park for free",401);

        if ($difEnd_Now < 8 )
        return $this->returnResponse("","You can't book, the working time hasn't started, the time starts at 08:00 AM ",401);


        if(Booking::where('num_car', $request->num_car)->where('country',$request->country)->first())
        return $this->returnResponse("","The car already has a reservation. You cannot book",400);



        $SlotController = app(SlotController::class);
        $slot=$SlotController-> Book_Slot_id($Request_admin->zone_id,$request->slot_id);
        if (!$slot)
            return $this->returnResponse("","No Slots Available for This Park",400);

        $book = new Booking();
        $book->country = $request->country;
        $book->num_car = $request->num_car;
        $book->slot_id = $slot->id;
        $zone = Zone::where('id', $slot->zone_id)->first();
        $book->hours = $request->hours;
        $book->date = Carbon::now()->today()->tz('Asia/Damascus');
        $book->startTime_book = Carbon::now()->tz('Asia/Damascus');
        $book->endTime_book = Carbon::now()->tz('Asia/Damascus')->addHour(intval($request->hours));
        $book->startTime_violation = $end_shift;

        $result = $book->save();



        if ($result) {
            $newBook = Booking::find($book->id);
            // $newBook->zonename = $zone->name;
            $newBook->park_spot = $slot->num_slot;
            $walletController = app(Wallet_AdminController::class);
            if($request->merge){
            $accept=$walletController-> withdraw($request->hours,"merge",$Request_admin->id,$book->id);
            $book->update([
                'merge'=>true,
            ]);
            $merge_slot= new MergeSlot();
            $merge_slot->slot_id=$request->slot_merge_id;
            $merge_slot->booking_id=$newBook->id;
            $merge_slot->save();

            }
            else {
                $accept=$walletController-> withdraw($request->hours,"hourly",$Request_admin->id);
            }
            if(!$accept){
                $SlotController->slot_is_empty($slot);
                $newBook->delete();
                return $this->returnResponse("","Error transaction",400);
            }


            $SlotController->unlocked($slot);


        return $this->returnResponse($book,"Successfully Book",200);
        }

        $SlotController->slot_is_empty($slot);

        return $this->returnResponse('',"oops..!!, You Can Not Book on This Park.",400);
    }
}
