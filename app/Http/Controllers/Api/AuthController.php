<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request){
        if(Auth::guard('sanctum')->check()){
            return redirectJson('LOGIN');
        }
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
    
        return $user->createToken((new DateTime())->getTimestamp())->plainTextToken;
    }
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
    
        return ;
    }
    public function clearTokens(Request $request){
        $request->user()->tokens()->delete();
        return ;
    }
}
