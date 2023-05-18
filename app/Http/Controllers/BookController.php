<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Car;
use App\Models\Slot;
use App\Models\User;
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
use App\Models\TypePay;

class BookController extends Controller{
use TraitApiResponse;

    public function create_book_user_previous(Request $request)
    {

        $Request_user = Auth::guard('user')->user();

        $end_shift=Carbon::now();
        $start_shift=Carbon::now();
        $end_shift->setTime(0,00);
        $time_now=Carbon::now()->setTimezone('Asia/Damascus')->subHours(5);
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

    public function create_book_user_now(Request $request)
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

    public function Get_Book_slot(Request $request)
    {
        $Request_admin = Auth::guard('user')->user();
        $slot= Slot::where('id', $request->slot_id)->first();

        if(!$slot->status){
            return $this->returnResponse("","You do not have a reservation",400);
        }


        $book= Booking::where('slot_id', $request->slot_id)->first();
        if(!$book){

            $merge= MergeSlot::where('slot_id', $request->slot_id)->first();
            $book= Booking::where('id', $merge->booking_id)->first();



        }
        $car_info= Car::where('num_car', $book->num_car)->where('country',$book->country)->first();
        if($car_info){
            $user_info= User::where('id', $car_info->user_id)->first();

            $book->name_user = $user_info->name;
            $book->phone = $user_info->phone;
            $book->type_car = $car_info->type;
            $book->color_car = $car_info->color;
        }
        else{
            $book->name_user = null;
            $book->phone = null;
            $book->type_car = null;
            $book->color_car = null;
        }
        $current_time=Carbon::now()->tz('Asia/Damascus');
        $calc_time = $current_time->diffInSeconds($book->endTime_book);
        $book->calc_time=$calc_time;

        return $this->returnResponse($book,"ok",200);

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
        if(!$status)
            return $this->returnResponse('',"Try again, thanks",400);

        $SlotController = app(SlotController::class);
        $slot=$SlotController-> slot_is_empty_id($book->slot_id);
        if($slot)
            return $this->returnResponse('',"Your reservation has been completed.",200);

            return $this->returnResponse('',"Try again, thanks",400);

    }

    public function create_outside_admin(Request $request)
    {

        $Request_admin = Auth::guard('admin')->user();

        $end_shift=Carbon::now();
        $start_shift=Carbon::now();
        $end_shift->setTime(0,00);
        $time_now=Carbon::now()->setTimezone('Asia/Damascus')->subHours(0);
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

        if($request->merge){
        $slot_merge=$SlotController->Book_Slot_name($Request_admin->zone_id,$request->slot_name);
            if(!$slot_merge){
                $slot_empty=$SlotController->slot_is_empty($slot);
                return $this->returnResponse("","No Slots merge Available for This Park",400);
                }
        }


        $book = new Booking();
        $book->country = $request->country;
        $book->num_car = $request->num_car;
        $book->slot_id = $slot->id;
        $book->hours = $request->hours;
        $book->date = Carbon::now()->today()->tz('Asia/Damascus');
        $book->startTime_book = Carbon::now()->tz('Asia/Damascus');
        $book->endTime_book = Carbon::now()->tz('Asia/Damascus')->addHour(intval($request->hours));
        $book->startTime_violation = $end_shift;
        $book->merge=$request->merge;
        $result = $book->save();

        if ($result) {
            $walletController = app(Wallet_AdminController::class);
            if($request->merge){
                $Merge_Slot_Controller = app(Merge_Slot_Controller::class);
                $accept=$Merge_Slot_Controller-> create_merge_slot($slot_merge->id,$book->id);
                if(!$accept){
                    $slot_empty=$SlotController->slot_is_empty($slot);
                    $slot_empty=$SlotController->slot_is_empty($slot_merge);
                    return $this->returnResponse("","try again",400);
                }
            $accept=$walletController-> withdraw($request->hours,"merge",$Request_admin->id,$book->id);
            }
            else {
                $accept=$walletController-> withdraw($request->hours,"hourly",$Request_admin->id,$book->id);
            }
            if(!$accept){
                $SlotController->slot_is_empty($slot);
                if($request->merge)
                    $SlotController->slot_is_empty($slot_merge);
                $book->delete();
                return $this->returnResponse("","Error transaction",400);
            }
            $SlotController->unlocked($slot);
            if($request->merge)
                $SlotController->unlocked($slot_merge);

            return $this->returnResponse('',"Successfully Book",201);
        }

        $SlotController->slot_is_empty($slot);
        $SlotController->slot_is_empty($slot_merge);


        return $this->returnResponse('',"oops..!!, You Can Not Book on This Park.",400);
    }

    public function create_violation_admin(Request $request){

        $Request_admin = Auth::guard('admin')->user();

        $end_shift=Carbon::now();
        $start_shift=Carbon::now();
        $end_shift->setTime(0,00);
        $time_now=Carbon::now()->setTimezone('Asia/Damascus')->subHours(5);
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

        if($request->merge){
            $slot_merge=$SlotController->Book_Slot_name($Request_admin->zone_id,$request->slot_name);
                if(!$slot_merge){
                    $slot_empty=$SlotController->slot_is_empty($slot);
                    return $this->returnResponse("","No Slots merge Available for This Park",400);
                    }
                }


        $book = new Booking();
        $book->country = $request->country;
        $book->num_car = $request->num_car;
        $book->slot_id = $slot->id;
        $book->hours = 0;
        $book->date = Carbon::now()->today()->tz('Asia/Damascus');
        $book->startTime_book = Carbon::now()->tz('Asia/Damascus');
        $book->endTime_book = $end_shift;
        $book->startTime_violation = Carbon::now()->tz('Asia/Damascus');
        $book->violation =true;
        $book->merge=$request->merge;
        $result = $book->save();

        if ($result) {
            $SlotController->unlocked($slot);
        if($request->merge){
            $Merge_Slot_Controller = app(Merge_Slot_Controller::class);
            $accept=$Merge_Slot_Controller-> create_merge_slot($slot_merge->id,$book->id);
            if(!$accept){
                $slot_empty=$SlotController->slot_is_empty($slot);
                $slot_empty=$SlotController->slot_is_empty($slot_merge);
                return $this->returnResponse("","try again",400);
            }
        }
        return $this->returnResponse('',"Successfully Book",201);
        }

        $SlotController->slot_is_empty($slot);

        return $this->returnResponse('',"oops..!!, You Can Not Book on This Park.",400);
    }

    public function End_Booking_All(Request $request)
    {
        $Request_admin = Auth::guard('admin')->user();

        $Merge_Slot_Controller = app(Merge_Slot_Controller::class);
        $SlotController = app(SlotController::class);


        $book= Booking::where('id', $request->book_id)->first();
        if(!$book)
            return $this->returnResponse('',"Your reservation has already expired",400);

        if($book->merge){
            $slot_merge=$Merge_Slot_Controller-> get_merge_slot($book->id);
            $SlotController->slot_is_empty_id($slot_merge);
            $status=$Merge_Slot_Controller-> delete_merge_slot($book->id);
            if(!$status)
                return $this->returnResponse('',"Try again, thanks",400);
        }
        if($book->violation){
            $endTime_book=Carbon::now()->tz('Asia/Damascus');
            $hours = $endTime_book->diffInHours($book->startTime_book);
            $hours += 1;

            $status=$book->update([
                'endTime_book'=>$endTime_book,
                'hours'=>$hours,

            ]);
            if(!$status)
                return $this->returnResponse('',"Try again, thanks",400);

            $walletController = app(Wallet_AdminController::class);
            $accept=$walletController-> withdraw($hours,"violation",$Request_admin->id,$book->id);

            if(!$accept){
                return $this->returnResponse("","Error transaction",400);
            }
        }

        $status=$book->delete();
        if(!$status)
            return $this->returnResponse('',"Try again, thanks",400);

        $slot=$SlotController-> slot_is_empty_id($book->slot_id);
        if($slot)
            return $this->returnResponse('',"Your reservation has been completed.",200);

        return $this->returnResponse('',"Try again, thanks",400);

    }

    public function update_booking_merge(Request $request)
    {
    }

    public function type_cost()
    {

        $type = TypePay::all();
        return $this->returnResponse($type,"Successfully",200);

    }
}
