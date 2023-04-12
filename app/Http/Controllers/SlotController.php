<?php

namespace App\Http\Controllers;

use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SlotController extends Controller
{
    public function Auto_Slot_id($zone_id){

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
    public function Book_Slot_id($zone_id,$slot_id){

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

    public function locked($slot){

        $slot->update([
            'status' =>true,
            'is_locked' => true
        ]);
    }
    public function unlocked($slot){

        $slot->update([
            'is_locked' => false
        ]);

    }
    public function slot_is_empty($slot){



        $slot->update([
            'status' =>false,
            'is_locked' => false,
        ]);

    }
    public function slot_is_empty_id($slot_id){


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

        $slot_admin = Slot::where('Zone_id', $Request_admin->zone_id)->get();
        if($slot_admin)
            return $this->returnResponse($slot_admin,"All Slot",200);

        return $this->returnResponse("","No fount",404);



    }

}
