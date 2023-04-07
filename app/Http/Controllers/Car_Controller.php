<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use App\Http\Traits\TraitApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;



class Car_Controller extends Controller
{
    use TraitApiResponse;

    public function Get_Cars_User(Request $request)
    {
        $Request_User = Auth::guard('user')->user();
        $cars=Car::where('user_id',$Request_User->id )->get();
        if(!$cars)
        return $this->returnResponse("","No car from this user",400);

        return $this->returnResponse($cars,"All car for user",200);
    }
    public function Create_Car(Request $request)
    {
        $rules=[
            "num_car"=> "required|max:6|min:6",
            "country"=>  "required",
            "type"=>  "required",
            "color"=>  "required",
        ];
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails()){
            return $this->returnResponse('',$validator->errors()->first(),400);
            }
        $Request_User = Auth::guard('user')->user();
        $get_car=Car::where('num_car', $request->num_car)->where('country',$request->country)->first();
        if($get_car)
        return $this->returnResponse("","This car already exists",400);

        $car=new Car;
        $car->country = $request->country;
        $car->num_car = $request->num_car;
        $car->type = $request->type;
        $car->color = $request->color;
        $car->user_id = $Request_User->id;
        $result=$car->save();
        if(!$result)
        return $this->returnResponse("","The car has not been saved, please try again",400);

        return $this->returnResponse("","Successfully",201);
    }
    public function Delete_Car(Request $request)
    {
        $Request_User = Auth::guard('user')->user();
        $cars=Car::where('num_car', $request->num_car)->where('country',$request->country)->where('user_id',$Request_User->id)->first();

        if(!$cars)
        return $this->returnResponse("","No fount",404);

        $cars->delete();
        return $this->returnResponse("","Successfully",201);
    }

}
