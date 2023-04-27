<?php

namespace App\Http\Controllers;

use App\Models\Deposite;
use Illuminate\Http\Request;
use Carbon\Carbon;


class Deposit_Controller extends Controller
{

    public function Create_Deposit($cost,$walletadmin_id,$walletuser_id)
    {

        $Deposit = new Deposite();
        $Deposit->date =Carbon::now()->today()->tz('Asia/Damascus'); ;
        $Deposit->cost=$cost ;
        $Deposit->walletadmin_id =$walletadmin_id ;
        $Deposit->walletuser_id =$walletuser_id ;


        $result = $Deposit->save();
        if(!$result)
            return false;

        return true;
    }
}
