<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\VendorController;
use Illuminate\Http\Request;
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
    return $request->user();
});
Route::middleware('admin.access')->group(function () {
    Route::get('all/vendors', [AdminController::class, 'allVendors']);
    Route::post('create/vendor', [AdminController::class, 'createVendor']);
    Route::patch('update/vendor/{vendor}', [AdminController::class, 'updateVendor']);
    Route::get('view/vendor/{vendor}', [AdminController::class, 'viewVendor']);
    Route::get('deactivate/vendor/{vendor}', [AdminController::class, 'deactivateVendor']);
    Route::get('reactivate/vendor/{vendor}', [AdminController::class, 'reactivateVendor']);
    Route::post('/update/credit/checker/status/{creditCheckerVerification}', [AdminController::class, 'updateCreditCheckerVerificationStatus']);
});
Route::post('auth/login', [VendorController::class, 'login'])->name('login');
Route::post('reset/password', [VendorController::class, 'resetPassword']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/logout', [VendorController::class, 'logout']);
    Route::post('customers', [CustomerController::class, 'store']);
    Route::get('customers/{telephone}', [CustomerController::class, 'show']);
    Route::post('store/order', [OrderController::class, 'storeOrder']);
    Route::post('/amortization/preview', [OrderController::class, 'previewAmortization']);
    Route::get('fetch/products', [OrderController::class, 'fetchProducts']);
    Route::get('vendor/orders', [OrderController::class, 'index']);
});
