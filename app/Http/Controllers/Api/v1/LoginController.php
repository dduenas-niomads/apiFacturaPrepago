<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class LoginController extends Controller
{
    //
    public function login(Request $request)
    {
        # code...
        $loginCredentials = $request->validate([
            "email" => "required|string",
            "password" => "required|string"
        ]);

        $loginCredentials['active'] = true;
        $loginCredentials['deleted_at'] = null;

        if (!Auth::attempt($loginCredentials)) {
            return response([
                "message" => "Invalid login credentials"
            ], 400);
        }

        $accessToken = Auth::user()->createToken('authToken')->accessToken;

        return response([
            "user" => User::with('activeLicense')
                ->with('role:id,name,description,code,actions')
                ->find(Auth::user()->id),
            "access_token" => $accessToken
        ]);
    }

    public function logout()
    {
        $userToken = Auth::user()->token();
        $userToken->revoke();
        return response()->json([
                'message' => 'Successfully logged out from current session',
                'redirect' => true
            ]);
    }

    public function logoutAll()
    {
        $userTokens = Auth::user()->tokens;
        foreach ($userTokens as $key => $token) {
            $token->revoke();
        }
        return response()->json([
                'message' => 'Successfully logged out from all sessions',
                'redirect' => true
            ]);
    }
}
