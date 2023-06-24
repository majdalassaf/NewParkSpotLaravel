<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\TypePay;
use App\Models\WalletUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\TraitApiResponse;
use App\Http\Controllers\TransactionController;

class Wallet_UserController extends Controller
{
use TraitApiResponse;
// منشان يقطع المصاري
    public function withdraw($hours,$type,$user_id,$book_id){
        $wallet_user = WalletUser::where('user_id', $user_id)->first();

        $typepay = TypePay::where("type",$type)->first();
        $cost= $typepay->cost;
        $total_cost = $hours * $cost;
        $new_amount= $wallet_user->amount - $total_cost;
        $date = Carbon::now()->today()->tz('Asia/Damascus');

        $transaction = app(TransactionController::class);
        $accept=$transaction-> Create_Transaction($book_id,$typepay->id,$total_cost,$date,$wallet_user->id);
        if (!$accept) {
        return false;
        }
        $wallet_user->update([
            'amount'=>$new_amount
            ]);
        return true;
}
public function withdraw_vip($hours,$type,$user_id,$book_id){
    $wallet_user = WalletUser::where('user_id', $user_id)->first();

    $typepay = TypePay::where("type","vip")->first();
    $vip= $typepay->cost;
    $typepay = TypePay::where("type",$type)->first();
    $cost= $typepay->cost;
    $total_cost = $hours * $cost;
    $total_cost = $total_cost + $vip;
    $new_amount= $wallet_user->amount - $total_cost;

    $date = Carbon::now()->today()->tz('Asia/Damascus');

    $transaction = app(TransactionController::class);
    $accept=$transaction-> Create_Transaction($book_id,$typepay->id,$total_cost,$date,$wallet_user->id);
    if (!$accept) {
    return false;
    }
    $wallet_user->update([
        'amount'=>$new_amount
        ]);
    return true;
}

// منشان يتاكد اذا في مصاري ولا لا
public function Check_Amount($hours,$type,$user_id){
    $wallet_user = WalletUser::where('user_id', $user_id)->first();
    $typepay = TypePay::where("type",$type)->first();
    $cost= $typepay->cost;

    $amount_needed = $hours * $cost;
    if ($wallet_user->amount <= $amount_needed) {
        return false;
    }
    return true;
}
public function Check_Amount_vip($hours,$type,$user_id){
    $wallet_user = WalletUser::where('user_id', $user_id)->first();
    $typepay = TypePay::where("type",'vip')->first();
    $vip= $typepay->cost;
    $typepay = TypePay::where("type",$type)->first();
    $cost= $typepay->cost;

    $amount_needed = ($hours * $cost)+$vip;
    if ($wallet_user->amount <= $amount_needed){
        return false;
    }
    return true;
}


public function create_wallet_user($user_id){
    $wallet=new WalletUser;
    $wallet->amount=0;
    $wallet->user_id =$user_id;
    $result=$wallet->save();
    if(!$result)
        return false;
    return true;

}
public function Get_Amount()
{
    $Request_user = Auth::guard('user')->user();
    $wallet_user = WalletUser::where('user_id', $Request_user->id)->first();
    if($wallet_user)
        return $this->returnResponse($wallet_user->amount,"your amount",200);

    return $this->returnResponse("","Not fount",400);


}
public function Delete_Wallet($user_id)
{
    $wallet_user = WalletUser::where('user_id', $user_id)->delete();
    if($wallet_user)
        return true;

    return false;

}


}
