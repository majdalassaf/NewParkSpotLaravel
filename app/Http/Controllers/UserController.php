<?php

namespace App\Http\Controllers;

use App\Http\Traits\TraitApiResponse;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\Process;

class UserController extends Controller{
use TraitApiResponse;

public function loginUser(Request $request){
   $rules=[
    "phone"=> "required|max:10|min:10|exists:users,phone",
    "password"=> "required|min:6"
   ];
   $validator=Validator::make($request->all(),$rules);
   if($validator->fails()){
    return $this->returnResponse('',$validator->errors()->first(),400);
   }

   $credentials= $request->only(['phone','password']);

   $token = Auth::guard('user')->attempt($credentials);
    if(!$token){
        return $this->returnResponse("","Some Error",400);
    }
    $user=Auth::guard('user')->user();
    $user -> token=$token;
   return $this->returnResponse($user,"Login Successfuly",200);;}



public function registerUser(Request $request) {

        $rules=[
            "phone"=> "required|max:10|min:10",
            "name"=>  "required",
            "password"=> "required|min:6"
           ];
           $validator=Validator::make($request->all(),$rules);
           if($validator->fails()){
            return $this->returnResponse('',$validator->errors()->first(),400);
           }
           $userfind=User::where('phone',$request->phone)->first();
           if ($userfind) {
           return $this->returnResponse("","The number has been registered",400);

           }

           $user=new User;
           $user->phone=$request->phone;
           $user->name = $request->name;
           $user->password=bcrypt($request->password);

           $result=$user->save();
            if (!$result) {
           return $this->returnResponse("","Some Error",400);

            }
            return $this->returnResponse('','User successfully registered ', 201);}
public function index(){

    return $this->returnResponse("hello","i am user",200);}
}
