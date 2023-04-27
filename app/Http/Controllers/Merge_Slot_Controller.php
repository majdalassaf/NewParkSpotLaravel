<?php

namespace App\Http\Controllers;

use App\Models\MergeSlot;
use Illuminate\Http\Request;

class Merge_Slot_Controller extends Controller
{
    public function create_merge_slot($slot_id,$book_id){
        $merge_slot= new MergeSlot();
        $merge_slot->slot_id=$slot_id;
        $merge_slot->booking_id=$book_id;
        $result=$merge_slot->save();
        if(!$result)
            return false;
        return true;
    }
    public function get_merge_slot($book_id){

        $merge_slot= MergeSlot::where('booking_id',$book_id)->first();
        if(!$merge_slot)
            return $merge_slot;
        return $merge_slot->slot_id;
    }
    public function delete_merge_slot($book_id){

        $merge_slot= MergeSlot::where('booking_id',$book_id)->first();

        $result=$merge_slot->delete();
        if(!$result)
            return false;
        return true;
    }

}
