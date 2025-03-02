<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Laravel\Sanctum\HasApiTokens;


class AdminController extends Controller
{
    // Admin Dashboard
    // public function index()
    // {
    //     return response()->json([
    //         'message' => 'Welcome to the admin dashboard',
    //         'admin' => Auth::user(),
    //     ]);
    // }
    public function login_user(Request $request){
        $validator = validator::make($request->all() ,[
            'email' =>'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->input('email'))->first();


        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials',
                'token' => "",
                'admin_type' => ""
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'admin_type' => $user->usertype
        ], 200);
    }
}

