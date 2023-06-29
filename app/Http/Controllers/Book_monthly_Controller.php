<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Slot;
use App\Models\User;
use App\Models\Zone;
use App\Models\BookMonthly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SlotController;
use App\Http\Controllers\Wallet_AdminController;


class Book_monthly_Controller extends Controller
{
    public function create_book_monthly_admin(Request $request)
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


        $SlotController = app(SlotController::class);
        $slot=$SlotController-> Book_Slot_id($Request_admin->zone_id,$request->slot_id);
        if (!$slot){
            return $this->returnResponse("","No Slots Available for This Park",400);
        }
        $user_id= User::where('phone', $request->phone)->first();
        if(!$user_id)
            return $this->returnResponse('',"Please check the entry number",400);

        $accept= BookMonthly::where('user_id', $user_id->id)->where('expired', false)->first();
        if($accept)
        return $this->returnResponse('',"He already has a monthly reservation",400);

        $book = new BookMonthly();
        $book->user_id = $user_id->id;
        $book->slot_id = $slot->id;
        $book->startTime_book = Carbon::now()->today()->tz('Asia/Damascus');
        $book->endTime_book = Carbon::now()->today()->tz('Asia/Damascus')->addDays(30);
        $book->vip=$request->vip;
        $result = $book->save();

        if ($result) {
            $walletController = app(Wallet_AdminController::class);
            if($request->vip){
                $accept=$walletController-> withdraw_monthly("monthly_vip",$Request_admin->id,$book->id);
            }
            else {
                $accept=$walletController-> withdraw_monthly("monthly",$Request_admin->id,$book->id);
            }
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




    public function Get_Book_monthly_user(Request $request)
    {
        $Request_user = Auth::guard('user')->user();

        $book= BookMonthly::where('user_id', $Request_user->id)->where('expired', false)->first();
        if(!$book)
            return $this->returnResponse("","You do not have a reservation",400);

        $current_time=Carbon::now()->tz('Asia/Damascus');
        $calc_time = $current_time->diffInDays($book->endTime_book);

        $slot = Slot::where('id',$book->slot_id)->first();
        $zone = Zone::where('id', $slot->zone_id)->first();
        $book->park_spot = $slot->num_slot;
        $book->zone_name = $zone->name;
        $book->calc_time=$calc_time;

        return $this->returnResponse($book,"You have a reservation",200);

    }
    public function Get_Book_monthly_admin(Request $request)
    {
        $Request_admin = Auth::guard('user')->user();
        $slot= Slot::where('id', $request->slot_id)->first();

        if(!$slot->status){
            return $this->returnResponse("","You do not have a reservation",400);
        }

        $book= BookMonthly::where('slot_id', $request->slot_id)->where('expired', false)->first();
        if(!$book)
        return $this->returnResponse("","You do not have a reservation",400);


        $user_info= User::where('id', $book->user_id)->first();
        if($user_info){
            $book->name_user = $user_info->name;
            $book->phone = $user_info->phone;

        }
        else{
            $book->name_user = null;
            $book->phone = null;
        }
        $current_time=Carbon::now()->tz('Asia/Damascus');
        $calc_time = $current_time->diffInDays($book->endTime_book);
        $book->calc_time=$calc_time;

        return $this->returnResponse($book,"ok",200);

    }
    public function End_Book_monthly(Request $request)
    {
        $book= BookMonthly::where('id', $request->book_id)->first();
        if(!$book)
            return $this->returnResponse('',"Your reservation has already expired",400);


        $status=$book->update([
                'expired'=>true,
                'endTime_book'=>Carbon::now()->today()->tz('Asia/Damascus'),
            ]);
        if(!$status)
            return $this->returnResponse('',"Try again, thanks",400);

        $SlotController = app(SlotController::class);
        $slot=$SlotController-> slot_is_empty_id($book->slot_id);
        if($slot)
            return $this->returnResponse('',"Your reservation has been completed.",200);

        return $this->returnResponse('',"Try again, thanks",400);

    }

}
