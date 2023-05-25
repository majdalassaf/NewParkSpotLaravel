<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Deposite;
use App\Models\WalletUser;

use App\Models\WalletAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



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
    public function Get_Deposit_User()
    {
        $Request_user = Auth::guard('user')->user();
        $wallet_user=WalletUser::where('user_id', $Request_user->id)->first();
        $Deposit_user = Deposite::where('walletuser_id', $wallet_user->id)->get();

        return $this->returnResponse($Deposit_user,"your Deposite",200);
    }
    public function Get_Deposit_Admin()
    {
        $Request_admin = Auth::guard('admin')->user();
        $wallet_admin=WalletAdmin::where('admin_id', $Request_admin->id)->first();
        $Deposit_admin = Deposite::where('walletadmin_id', $wallet_admin->id)->get();

        return $this->returnResponse($Deposit_admin,"your Deposite",200);
    }
}
