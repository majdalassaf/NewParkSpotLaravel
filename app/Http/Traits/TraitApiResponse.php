<?php

namespace App\Http\Traits;

use App\User;



trait TraitApiResponse {

    public function returnResponse($data='null',$msg='null',$status='null'){
        $array=[
            'data'=>$data,
            'msg'=>$msg,
            'status'=>$status
        ];
        return response($array,$status);
    }

    public function checkID($nameModel,$id){
        $data=$nameModel::find($id);
        if(!$data){
            return $this->getResponce('null','is not Found . . !',403);
        }
        return ($data);
    }
}
