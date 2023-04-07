<?php

namespace App\Http\Middleware;

use App\Http\Traits\TraitApiResponse;
use Closure;
use Exception;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class AdminMiddleware extends BaseMiddleware
{
    use TraitApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$guard=null)
    {
        if ($guard!=null) {
            auth()->shouldUse($guard);
            $token = $request->header('token');
            $request->headers->set('token',$token, true);
            $request->headers->set('Authorization','Bearer '.$token,true);
            try {
                $user = JWTAuth::parseToken()->authenticate();
                // $user = ;
            } catch (TokenExpiredException $e) {
                return $this->returnResponse("","Unauthenticated",401);

            }
            // catch (TokenInvalidException $e) {
            //     return $this->returnResponse("","Token is invalid",400);
            // }
                catch  (Exception $e){
              return   $this->returnResponse("","Authorization Token not found",401);
            }
        }

            if (!Auth::guard('admin')->user())
               return $this->returnResponse("","You do not have access permission",401);

            return $next($request);
    }
}
