<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\TraitApiResponse;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller{
use TraitApiResponse;

    public function loginUser(Request $request){
        $rules=[
        "phone"=> "required|max:10|min:10|exists:users,phone",
        "password"=> "required|min:6"
        ];
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
            return $this->returnResponse('',$validator->errors()->first(),400);

        $credentials= $request->only(['phone','password']);
        $token = Auth::guard('user')->attempt($credentials);
        if(!$token){
            return $this->returnResponse("","Some Error",400);
        }
        $user=Auth::guard('user')->user();
        $user -> token=$token;
        return $this->returnResponse($user,"Login Successfully",200);;
    }



    public function registerUser(Request $request) {
        $rules=[
            "phone"=> "required|max:10|min:10",
            "name"=>  "required",
            "password"=> "required|min:6"
        ];
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
            return $this->returnResponse('',$validator->errors()->first(),400);

        $user_find=User::where('phone',$request->phone)->first();
        if ($user_find)
            return $this->returnResponse("","The number has been registered",400);

        $user=new User;
        $user->phone=$request->phone;
        $user->name = $request->name;
        $user->password=bcrypt($request->password);
        $result=$user->save();

        $Wallet_User = app(Wallet_UserController::class);
        $result_wallet=$Wallet_User-> create_wallet_user($user->id);
        if(!$result_wallet){
            $user_delete=User::where('id', $user->id)->delete();
            return $this->returnResponse("","The wallet could not be created",400);
        }

        if (!$result)
            return $this->returnResponse("","Some Error",400);

        return $this->returnResponse('','User successfully registered ', 201);
    }
    public function index(){

        return $this->returnResponse("hello","i am user",200);
    }

    public function Update_User_Password(Request $request){
        $rules=[
            "phone"=> "required|max:10|min:10",
            "password"=> "required|min:6"
        ];
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
            return $this->returnResponse('',$validator->errors()->first(),400);
        $user=User::where('phone', $request->phone)->first();
        if(Hash::check($request->password,$user->password))
            return $this->returnResponse('','This is the same password', 400);
        $result=$user->update([
            'password'=>bcrypt($request->password)
        ]);
        if($result)
            return $this->returnResponse('','Password has been changed', 200);

        return $this->returnResponse('','Password has not been changed', 400);

    }
    public function Update_User_Phone(Request $request){
        $rules=[
            "phone"=> "required|max:10|min:10"
        ];
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
            return $this->returnResponse('',$validator->errors()->first(),400);

        $Request_user = Auth::guard('user')->user();

        $user=User::where('id', $Request_user->id)->first();
        if($Request_user->phone == $request->phone)
            return $this->returnResponse('','This is the same phone', 400);
        $result=$user->update([
            'phone'=>$request->phone
        ]);
        if($result)
            return $this->returnResponse('','phone has been changed', 200);

        return $this->returnResponse('','phone has not been changed', 400);

    }

    public function Update_User_Name(Request $request){
        $rules=[
            "name"=> "required"
        ];
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
            return $this->returnResponse('',$validator->errors()->first(),400);

        $Request_user = Auth::guard('user')->user();

        $user=User::where('id', $Request_user->id)->first();
        if($Request_user->name == $request->name)
            return $this->returnResponse('','This is the same name', 400);
        $result=$user->update([
            'name'=>$request->name
        ]);
        if($result)
            return $this->returnResponse('','name has been changed', 200);

        return $this->returnResponse('','name has not been changed', 400);

    }
    public function Get_User(){
        $Request_user = Auth::guard('user')->user();
        return $this->returnResponse($Request_user,'ok', 200);

    }
    public function User_Delete(Request $request)
    {
        // $user=User::where('id', $request->id)->delete();
        // $Wallet_Delete = app(WalletUser::class);
        // $result_Delete=$Wallet_Delete-> Delete_Wallet($user->id);

        // if($user && $result_Delete)
        //     return $this->returnResponse('','The user has been deleted', 200);

        // return $this->returnResponse('','The user has been deleted', 200);

    }

}
