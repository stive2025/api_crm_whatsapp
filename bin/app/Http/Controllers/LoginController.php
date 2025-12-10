<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request){
        
        $user=User::where('email',$request->email)->first();

        if($user && password_verify($request->password,$user->password)){
            $token=$user->createToken('login',json_decode($user->abilities));
            
            return response()->json([
                "status"=>200,
                "token"=>$token,
                "user"=>$user
            ],200);
        }

        return response()->json([
            'message'=>'No autorizado'
        ],401);

    }
}