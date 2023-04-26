<?php

namespace App\Http\Controllers;

use App\Models\TypePay;
use App\Models\WalletAdmin;
use Illuminate\Http\Request;
use App\Http\Traits\TraitApiResponse;

class Wallet_AdminController extends Controller
{

    use TraitApiResponse;
// منشان يقطع المصاري
    public function withdraw($hours,$type,$admin_id,$book_id){
        $wallet_Admin = WalletAdmin::where('admin_id', $admin_id)->first();


        $typepay = TypePay::where("type",$type)->first();
        $cost= $typepay->cost;
        $total_cost = $hours * $cost;
        $new_amount= $wallet_Admin->amount + $total_cost;

        $transaction = app(TransactionController::class);
        $accept=$transaction-> Create_Transaction_admin($book_id,$typepay->id,$total_cost,$wallet_Admin->id);
        if (!$accept) {
        return false;
        }
        $wallet_Admin->update([
            'amount'=>$new_amount
            ]);
        return true;
}
public function create_wallet_Admin($admin_id){
    $wallet=new WalletAdmin;
    $wallet->amount=0;
    $wallet->admin_id =$admin_id;
    $result=$wallet->save();
    if(!$result)
        return false;
    return true;

}
}
