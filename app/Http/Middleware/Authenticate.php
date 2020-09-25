<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;
    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {   
        $token = JWTAuth::getToken();
        if(!$token){
            return response()->json([
                'error' => 'Token not Provided',
            ], 401);
        }
        
        
        // if ($this->auth->guard($guard)->guest()) {
        //     return response('Unauthorized dari handle.'.var_dump($this->auth->guard($guard)->guest()), 401);
        // }
        try{
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            // \Log::debug('token expired');
            // return response()->json([
            //     'error' => 'token_expired',
            // ], 401);
            try {
                $customClaims = [];
                $refreshedToken = JWTAuth::claims($customClaims)
                    ->refresh(JWTAuth::getToken());
            } catch (TokenExpiredException $e) {
                return response()->json([
                    'error' => 'token_expired',
                    'refresh' => false,
                ], 401);
            }
            return response()->json([
                'error' => 'token_expired_and_refreshed',
                'refresh' => [
                    'token' => $refreshedToken,
                ],
            ], 401);
        } catch (TokenInvalidException $e) {
            \Log::debug('token invalid');
            return response()->json([
                'error' => 'token_invalid',
            ], 401);
        } catch (TokenBlacklistedException $e) {
            \Log::debug('token blacklisted');
            return response()->json([
                'error' => 'token_blacklisted',
            ], 401);
        } catch (JWTException $e) {
            \Log::debug('token absent');
            return response()->json([
                'error' => 'token_absent',
            ], 401);

        }
        return $next($request);
    }
}
