<?php

namespace App\Http\Controllers;

use App\Models\Slot;
use Illuminate\Http\Request;

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
            'is_locked' => false
        ]);

    }

}
