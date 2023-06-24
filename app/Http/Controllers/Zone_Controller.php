<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use App\Http\Traits\TraitApiResponse;
use Illuminate\Support\Facades\Validator;

class Zone_Controller extends Controller
{
use TraitApiResponse;

public function Get_All_Zone()
{
    $zone = Zone::all();
    return $this->returnResponse($zone,"All Zone",200);
}
public function Add_Zone(Request $request)
{
    $rules=[
        "type"=> "required",
        "name"=>  "required",
        "lat"=>  "required",
        "lan"=>  "required",
    ];
    $validator=Validator::make($request->all(),$rules);
    if($validator->fails())
        return $this->returnResponse('',$validator->errors()->first(),400);
    $zone=new Zone;
    $zone->type=$request->type;
    $zone->name = $request->name;
    $zone->lat=$request->lat;
    $zone->lan=$request->lan;
    $result=$zone->save();

    if($result)
        return $this->returnResponse('',"Successfully Add Zone",201);

    return $this->returnResponse('',"oops..!!, You Can Not Add Zone.",400);


}
public function Delete_Zone(Request $request){
    $zone=Zone::where('name',$request->name)->first();
    if(!$zone)
    return $this->returnResponse('',"does not already exist",400);
    $status=$zone->delete();

    if($status)
        return $this->returnResponse('',"Successfully Delete Zone",200);

    return $this->returnResponse('',"oops..!!, You Can Not Delete.",400);




}

}
