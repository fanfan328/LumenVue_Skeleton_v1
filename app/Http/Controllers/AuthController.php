<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;


class AuthController extends Controller
{
    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function register(Request $request)
    {
        //validate incoming request 
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|confirmed',
            'role' => 'required',
            'username' => 'required'
        ]);
        try {
            $user = new User;
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->role = $request->input('role');
            $user->username = $request->input('username');
            $plainPassword = $request->input('password');
            $user->password = app('hash')->make($plainPassword);
            $user->save();
            //return successful response
            return response()->json(['user' => $user, 'message' => 'CREATED'], 201);
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'User Registration Failed!'], 409);
        }
    }
    /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */
    public function login(Request $request)
    {
          //validate incoming request 
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);
        $credentials = $request->only(['email', 'password']);
        
        $selectedUser = User::where('email', '=', $request->input('email'))->first();
        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token, $selectedUser->role);
    }
    public function token(){
        $token = JWTAuth::getToken();
        if(!$token){
            throw new BadRequestHtttpException('Token not provided');
        }
        try{
            $token = JWTAuth::refresh($token);
        } catch (TokenExpiredException $e) {
            \Log::debug('token expired');
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
        return $this->respondWithToken($token, 2);
    }
    
}
