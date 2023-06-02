<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function adminregister()
    {
       
       $validator = Validator::make(request()->all(), [
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'phone'     => 'required|numeric|digits_between:10,12',
            'password'  => 'required|min:8|confirmed',
            
        ]);
        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //create user
        $user = Admin::create([
            'name'      => request('name'),
            'email'     => request('email'),
            'phone'     => request('phone'),
            'tipeAkun'  => 'Admin',
            'password'  => bcrypt(request('password')),
            
        ]);
        


        //return response JSON user is created
        if ($user) {
            return response()->json([
                'success' => true,
                'user'    => $user,
            ], 201);
        }

        //return JSON process insert failed 
        return response()->json([
            'success' => false,
        ], 409);
    }

    public function adminlogin()
    {
        $validator = Validator::make(request()->all(), [

            'email'     => 'required|email',
            'password'  => 'required',
            
        ]);
        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = request(['email', 'password']);

        if (! $token = auth()->guard('admin-api')->attempt($credentials)) {
            return response()->json(['error' => 'Email atau Password Salah'], 401);
        }

        $user = auth()->guard('admin-api')->user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard('admin-api')->factory()->getTTL() * 60,
            'user' => $user
        ]);
    }

    public function adminme()
    {
        return response()->json(auth()->guard('admin-api')->user());
    }

    public function adminlogout()
    {
        auth()->guard('admin-api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function adminrefresh()
    {
        return $this->respondWithToken(auth()->guard('admin-api')->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard('admin-api')->factory()->getTTL() * 60,
        ]);
    }

}
