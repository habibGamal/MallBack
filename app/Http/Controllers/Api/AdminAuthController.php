<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function login(Request $request){
        if(Auth::guard('admin')->check()){
            return redirectJson('LOGIN');
        }
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        $user = Admin::where('email', $request->email)->first();
    
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
    
        return $user->createToken((new DateTime())->getTimestamp())->plainTextToken;
    }
    public function logout(Request $request){
        $request->user('admin')->currentAccessToken()->delete();
    
        return ;
    }
    public function clearTokens(Request $request){
        $request->user('admin')->tokens()->delete();
        return ;
    }
}
