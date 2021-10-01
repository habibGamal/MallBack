<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout',[AuthController::class,'logout']);
    Route::post('/clear-tokens',[AuthController::class,'clearTokens']);
});

Route::get('/state',function(Request $request){
    $auth = Auth::guard('sanctum')->check() ? 'true':'false';
    return $auth;
});

Route::middleware(['throttle:60,1'])->group(function () {
    Route::apiResource('category',CategoryController::class);
});

Route::apiResource('product',ProductController::class);

Route::post('/login',[AuthController::class,'login']);

Route::get('/getheaders',function(Request $request){
    dumph($request->header());
});