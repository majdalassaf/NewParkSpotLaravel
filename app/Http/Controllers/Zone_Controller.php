<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use App\Http\Traits\TraitApiResponse;

class Zone_Controller extends Controller
{
use TraitApiResponse;

 public function Get_All_Zone()
 {
    $zone = Zone::all();
    return $this->returnResponse($zone,"All Zone",200);
}
}
