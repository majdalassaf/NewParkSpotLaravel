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
       if($validator->fails()){
        return $this->returnResponse('',$validator->errors()->first(),400);
       }

       $credentials= $request->only(['phone','password']);

       $token = Auth::guard('admin')->attempt($credentials);
        if(!$token){
            return $this->returnResponse("","Some Error",400);
        }
        $admin=Auth::guard('admin')->user();
        $admin -> token=$token;
       return $this->returnResponse($admin,"Login Successfuly",200);;
    }


public function index()
    {

        return $this->returnResponse("hello","i am admin",200);
    }

}
