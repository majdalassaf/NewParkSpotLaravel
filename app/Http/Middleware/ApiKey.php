<?php

namespace App\Http\Middleware;

use App\Http\Traits\TraitApiResponse;



use Closure;
class ApiKey
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
use TraitApiResponse;

    public function handle($request, Closure $next)
    {

        // $token = $request->header('token');
        if ($request->header('api_key') != env("Api_key",123456789)) {
        return $this->returnResponse('','Unauthenticated',401);

        }
        return $next($request);


    }
}
