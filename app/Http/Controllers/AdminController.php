<?php

namespace App\Http\Controllers;

use App\Http\Traits\TraitApiResponse;
use App\Models\Admin;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class AdminController extends Controller
{
    use TraitApiResponse;

public function loginAdmin(Request $request)
    {
    $rules=[
        "phone"=> "required|max:10|min:10|exists:admins,phone",
        "password"=> "required|min:6"


    ];
    $validator=Validator::make($request->all(),$rules);
    if($validator->fails())
        return $this->returnResponse('',$validator->errors()->first(),400);


    $credentials= $request->only(['phone','password']);

    $token = Auth::guard('admin')->attempt($credentials);
    if(!$token)
        return $this->returnResponse("","Some Error",400);

    $admin=Auth::guard('admin')->user();
    $admin -> token=$token;
    return $this->returnResponse($admin,"Login Successfully",200);;
    }


public function index()
    {
        return $this->returnResponse("hello","i am admin",200);
    }



public function registerAdmin(Request $request) {
    $rules=[
        "phone"=> "required|max:10|min:10",
        "name"=>  "required",
        "password"=> "required|min:6",
        "zone_id"=>  "required",

    ];
    $validator=Validator::make($request->all(),$rules);
    if($validator->fails())
        return $this->returnResponse('',$validator->errors()->first(),400);

    $admin_find=Admin::where('phone',$request->phone)->first();
    if ($admin_find)
        return $this->returnResponse("","The number has been registered",400);

    $admin=new Admin;
    $admin->phone=$request->phone;
    $admin->name = $request->name;
    $admin->password=bcrypt($request->password);
    $admin->zone_id=$request->zone_id;
    $result=$admin->save();

    $Wallet_Admin = app(Wallet_AdminController::class);
    $result_wallet=$Wallet_Admin-> create_wallet_Admin($admin->id);
    if(!$result_wallet){
        $admin_delete=Admin::where('id', $admin->id)->delete();
        return $this->returnResponse("","The wallet could not be created",400);
    }

    if (!$result)
        return $this->returnResponse("","Some Error",400);

    return $this->returnResponse('','Admin successfully registered ', 201);
}
}
