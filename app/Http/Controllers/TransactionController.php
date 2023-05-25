<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\WalletAdmin;
use App\Models\WalletUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TransactionController extends Controller
{
    public function Create_Transaction($book_id,$Type_pay_id,$date,$total_cost,$Wallet_user_id=null)
    {
        $transaction = new Transaction();
        $transaction->book_id =$book_id ;
        $transaction->typepay_id =$Type_pay_id ;
        $transaction->cost =$total_cost ;
        $transaction->date =$date ;
        $transaction->walletuser_id =$Wallet_user_id ;
        $result = $transaction->save();
        if(!$result)
            return false;

        return true;
    }
    public function Create_Transaction_admin($book_id,$Type_pay_id,$total_cost,$date,$Wallet_admin_id=null)
    {

        $transaction = new Transaction();
        $transaction->book_id =$book_id ;
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
    public function Get_Transaction_User()
    {
        $Request_user = Auth::guard('user')->user();
        $wallet_user=WalletUser::where('user_id', $Request_user->id)->first();
        $Transaction_user = Transaction::where('walletuser_id', $wallet_user->id)->get();

        return $this->returnResponse($Transaction_user,"your Transaction",200);
    }
    public function Get_Transaction_Admin()
    {
        $Request_admin = Auth::guard('admin')->user();
        $wallet_admin=WalletAdmin::where('admin_id', $Request_admin->id)->first();
        $Transaction_user = Transaction::where('walletadmin_id', $wallet_admin->id)->get();

        return $this->returnResponse($Transaction_user,"your Transaction",200);
    }
}

