<?php

namespace App\Http\Controllers;

use App\Models\WalletUser;
use App\Models\WalletAdmin;
use Illuminate\Http\Request;
use App\Models\TransactionMonthly;
use Illuminate\Support\Facades\Auth;

class Transaction_monthly_Controller extends Controller
{
    public function Create_Transaction_admin($book_id,$Type_pay_id,$total_cost,$date,$Wallet_admin_id=null)
    {

        $transaction = new TransactionMonthly();
        $transaction->bookmonthly_id =$book_id ;
        $transaction->typepay_id =$Type_pay_id ;
        $transaction->cost =$total_cost ;
        $transaction->date =$date ;
        $transaction->walletadmin_id =$Wallet_admin_id ;
        $result = $transaction->save();
        return $transaction;

        if(!$result)
            return false;

        return true;
    }

    public function Get_Transaction_Admin()
    {
        $Request_admin = Auth::guard('admin')->user();
        $wallet_admin=WalletAdmin::where('admin_id', $Request_admin->id)->first();
        $Transaction_user = TransactionMonthly::where('walletadmin_id', $wallet_admin->id)->get();

        return $this->returnResponse($Transaction_user,"your Transaction",200);
    }
}
