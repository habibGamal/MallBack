<?php

use App\Http\Controllers\Api\UserAuthController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\RegisterAdminController;
use App\Http\Controllers\Api\RegisterUserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

// manage authentication

// => user
// - login
// - register
Route::post('/login', [UserAuthController::class, 'login']);
Route::post('/register', [RegisterUserController::class, 'register']);
// => admin login
Route::post('/admin-login', [AdminAuthController::class, 'login']);
// => logout
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin-logout', [AdminAuthController::class, 'logout']);
    Route::post('/logout', [UserAuthController::class, 'logout']);
    Route::post('/clear-tokens', [UserAuthController::class, 'clearTokens']);
});

// => get state of current user
Route::get('/state', function () {
    $user = Auth::guard('user')->check();
    $admin = Auth::guard('admin')->check();
    return ["user" => $user, "admin" => $admin];
});


// api resources

// => category
Route::middleware(['throttle:60,1'])->group(function () {
    Route::apiResource('category', CategoryController::class);
});
// => product
Route::apiResource('product', ProductController::class);
Route::post('/product/deleteList', [ProductController::class, 'destroyList']);


Route::post('/getRowPicture', function (Request $request) {
    $paths = [];
    foreach ($request->paths as $key => $path) {
        $full_path = Storage::path($path);
        $base64 = base64_encode(Storage::get($path));
        $image_data = 'data:' . mime_content_type($full_path) . ';base64,' . $base64;
        $paths[] = $image_data;
    }
    return $paths;
});
// => store
Route::apiResource('store', StoreController::class);
// => branch
Route::apiResource('branch', BranchController::class);
// => cart
Route::apiResource('cart-item', CartItemsController::class);


// testing area

Route::post('/test', function (Request $request) {
    $t = base64_encode(Storage::get($request->path));
    dumph($t);
    return '';
});
