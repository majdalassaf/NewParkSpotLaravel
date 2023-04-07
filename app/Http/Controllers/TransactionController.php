<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TransactionController extends Controller
{
public function Create_Transaction($book_id,$Type_pay_id,$total_cost,$Wallet_user_id=null,$Wallet_admin_id=null)
{

    $transaction = new Transaction();
    $transaction->book_id =$book_id ;
    $transaction->typepay_id =$Type_pay_id ;
    $transaction->cost =$total_cost ;
    $transaction->walletuser_id =$Wallet_user_id ;
    $transaction->walletadmin_id =$Wallet_admin_id ;


    $result = $transaction->save();
    if(!$result)
    return false;

    return true;
}
public function Get_Transaction()
{
    $Request_user = Auth::guard('user')->user();
    $Transaction_user = Transaction::where('walletuser_id', $Request_user->id)->get();

    return $this->returnResponse($Transaction_user,"your Transaction",200);


}
}
