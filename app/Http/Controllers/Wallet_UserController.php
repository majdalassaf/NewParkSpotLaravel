<?php

namespace App\Http\Controllers;

use App\Models\TypePay;
use App\Models\WalletUser;
use Illuminate\Http\Request;
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

        $transaction = app(TransactionController::class);
        $accept=$transaction-> Create_Transaction($book_id,$typepay->id,$total_cost,$wallet_user->id);
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





}
