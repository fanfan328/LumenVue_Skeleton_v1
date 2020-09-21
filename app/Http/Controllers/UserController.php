<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use  App\User;

class UserController extends Controller
{
     /**
     * Instantiate a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get the authenticated User.
     *
     * @return Response
     */
    public function profile()
    {
        return response()->json(['user' => Auth::user()], 200);
    }

    /**
     * Get all User.
     *
     * @return Response
     */
    public function allUsers()
    {
         return response()->json(['users' =>  User::all()], 200);
    }

    /**
     * Get one user.
     *
     * @return Response
     */
    public function singleUser($id)
    {
        try {
            $user = User::findOrFail($id);

            return response()->json(['user' => $user], 200);

        } catch (\Exception $e) {

            return response()->json(['message' => 'user not found!'], 404);
        }

    }

    public function destroy($id){
        $user = User::whereId($id)->first();
            $user->delete();

        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'User Berhasil Dihapus!'
            ], 200);
        }
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'role' => 'required',
            'username' => 'required'
        ]);

        try{
            $post = User::whereId($request->input('id'))->update([
                'name'     => $request->input('name'),
                'email'   => $request->input('email'),
                'role'   => $request->input('role'),
                'username'   => $request->input('username'),
            ]);

            if ($post) {
                return response()->json([
                    'success' => true,
                    'message' => 'Post Berhasil Diupdate!'
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Post Gagal Diupdate!',
                ], 400);
            }
        }catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Semua Kolom Wajib Diisi!',
                'data'   => $validator->errors()
            ],401);
        }

    }
}
