<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TypePay;
use App\Models\WalletUser;
use App\Models\WalletAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\TraitApiResponse;


class Wallet_AdminController extends Controller
{

    use TraitApiResponse;
    public function create_wallet_Admin($admin_id){
        $wallet=new WalletAdmin;
        $wallet->amount=0;
        $wallet->admin_id =$admin_id;
        $result=$wallet->save();
        if(!$result)
            return false;
        return true;

    }
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
    //تحويل للUSER
    public function Deposit(Request $request)
    {
        $user= User::where('phone', $request->phone)->first();
        if(!$user)
        return $this->returnResponse('',"The entry number is wrong or does not exist",400);

        $wallet_user = WalletUser::where('user_id', $user->id)->first();
        if(!$wallet_user)
            return $this->returnResponse('',"Try again, thanks",400);

        $Request_admin = Auth::guard('admin')->user();
        $wallet_Admin = WalletAdmin::where('admin_id', $Request_admin->id)->first();
        if(!$wallet_Admin)
            return $this->returnResponse('',"Try again, thanks",400);


        $status=$wallet_Admin->update([
            'amount'=>$wallet_Admin->amount + $request->money,
        ]);

        $status=$wallet_user->update([
            'amount'=>$wallet_user->amount + $request->money,
        ]);

        $Deposit_Controller = app(Deposit_Controller::class);
        $accept=$Deposit_Controller-> Create_Deposit($request->money,$wallet_user->id,$wallet_Admin->id);

        return $this->returnResponse('',"Successfully Deposit",200);

    }

}
