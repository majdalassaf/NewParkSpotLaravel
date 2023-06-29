<?php

namespace App\Http\Controllers;

use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\TraitApiResponse;
use App\Models\Booking;
use App\Models\BookMonthly;
use App\Models\MergeSlot;
use App\Models\Zone;
use Illuminate\Support\Facades\Validator;

class SlotController extends Controller
{
use TraitApiResponse;

    public function Auto_Slot_id($zone_id)
    {

        $slot = Slot::where('status', false)
        ->where('is_locked', false)
        ->where('zone_id',$zone_id)
        ->lockForUpdate()
        ->first();
        if (!$slot)
            return false;
        $this->locked($slot);
        return $slot;
    }
    public function Book_Slot_id($zone_id,$slot_id)
    {

        $slot = Slot::where('status', false)
        ->where('is_locked', false)
        ->where('zone_id',$zone_id)
        ->where('id',$slot_id)
        ->lockForUpdate()
        ->first();
        if (!$slot)
            return false;
        $this->locked($slot);
        return $slot;
    }
    public function Book_Slot_name($zone_id,$slot_name)
    {

        $slot = Slot::where('status', false)
        ->where('is_locked', false)
        ->where('zone_id',$zone_id)
        ->where('num_slot',$slot_name)
        ->lockForUpdate()
        ->first();
        if (!$slot)
            return false;
        $this->locked($slot);
        return $slot;
    }

    public function locked($slot)
    {

        $slot->update([
            'status' =>true,
            'is_locked' => true
        ]);
    }
    public function unlocked($slot)
    {

        $slot->update([
            'is_locked' => false
        ]);

    }
    public function slot_is_empty($slot)
    {

        $status=$slot->update([
            'status' =>false,
            'is_locked' => false,
        ]);
        if($status)
            return true;

        return false;

    }
    public function slot_is_empty_id($slot_id)
    {
        $slot = Slot::where('id', $slot_id)->first();
        $slot->update([
            'status' =>false,
            'is_locked' => false,
        ]);
        if($slot)
            return true;
        return false;

    }

    public function Get_All_Slot()
    {
        $Request_admin = Auth::guard('admin')->user();

        $slot_admin = Slot::where('zone_id', $Request_admin->zone_id)->get();
        foreach($slot_admin as $i){
            if($i->status==1){
                $slot_status = Booking::where('slot_id', $i->id)->first();
                $slot_monthly = BookMonthly::where('slot_id', $i->id)->first();
                if($slot_status){
                    $i->country=$slot_status->country;
                    $i->num_car=$slot_status->num_car;
                    $i->book_monthly=false;
                }
                elseif($slot_monthly){
                    $i->country=null;
                    $i->num_car=null;
                    $i->book_monthly=true;

                }
                else{
                    $merge = MergeSlot::where('slot_id', $i->id)->first();
                    $slot_status = Booking::where('id', $merge->booking_id)->first();
                    $i->country=$slot_status->country;
                    $i->num_car=$slot_status->num_car;
                    $i->book_monthly=false;
                }
            }
            else{
                $i->country=null;
                $i->num_car=null;
                $i->book_monthly=false;

            }
        }
        if($slot_admin)
            return $this->returnResponse($slot_admin,"All Slot",200);

        return $this->returnResponse("","No fount",404);

    }

    public function Add_Slot(Request $request)
    {
        $rules=[
            "num_slot"=> "required",
            "zone_id"=> "required",
        ];
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
            return $this->returnResponse('',$validator->errors()->first(),400);
        $zone=Zone::where('id',$request->zone_id)->first();
        if(!$zone)
            return $this->returnResponse('',"oops..!!, The zone does not exist",400);
        $slot=new Slot;
        $slot->num_slot=$request->num_slot;
        $slot->status =0;
        $slot->zone_id=$request->zone_id;
        $slot->is_locked=0;
        $result=$slot->save();
        if($result)
            return $this->returnResponse('',"Successfully Add Slots",201);

        return $this->returnResponse('',"oops..!!, You Can Not Add Zone.",400);
    }

    public function Add_Slots(Request $request)
    {
        $rules=[
            "num_slot"=> "required",
            "number"=> "required",
            "zone_id"=> "required",
        ];
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
            return $this->returnResponse('',$validator->errors()->first(),400);

        $zone=Zone::where('id',$request->zone_id)->first();
        if(!$zone)
        return $this->returnResponse('',"oops..!!, The zone does not exist",400);

        for ($i = 0; $i < $request->number; $i++){
            $slot=new Slot;
            $slot->num_slot=($request->num_slot.$i);
            $slot->status =0;
            $slot->zone_id=$request->zone_id;
            $slot->is_locked=0;
            $result=$slot->save();
        }
        if($result)
            return $this->returnResponse('',"Successfully Add Slots",201);

        return $this->returnResponse('',"oops..!!, You Can Not Add Zone.",400);
    }


    public function Delete_Slot(Request $request)
    {
        $slot=Slot::where('num_slot',$request->num_slot)->where('zone_id',$request->zone_id)->first();
        if(!$slot)
            return $this->returnResponse('',"does not already exist",400);
        $status=$slot->delete();
        if(!$status)
            return $this->returnResponse('',"oops..!!, You Can Not Delete.",400);

        return $this->returnResponse('',"Successfully Delete Slot",200);

    }

}
