<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return 'authenticated';
});

Route::get('/state',function(Request $request){
    $auth = Auth::check() ? 'true':'false';
    return $auth;
});
Route::middleware(['throttle:60,1'])->group(function () {
    Route::apiResource('category',CategoryController::class);
});
Route::apiResource('product',ProductController::class);