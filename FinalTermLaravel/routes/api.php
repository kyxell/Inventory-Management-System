<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AccountDataController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InventoryActionController;
use App\Http\Controllers\Api\InventoryDataController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('web')->prefix('process')->group(function () {
    Route::post('login_process.php', [AuthController::class, 'login']);
    Route::post('register_process.php', [AuthController::class, 'register']);
    Route::post('logout_process.php', [AuthController::class, 'logout'])->middleware('auth');

    Route::middleware('auth')->group(function () {
        Route::get('get_data.php', [InventoryDataController::class, 'index']);
        Route::post('add_process.php', [InventoryActionController::class, 'store']);
        Route::post('edit_process.php', [InventoryActionController::class, 'update']);
        Route::post('delete_process.php', [InventoryActionController::class, 'destroy']);
        Route::post('import_process.php', [InventoryActionController::class, 'import']);
        Route::post('deliver_process.php', [InventoryActionController::class, 'deliver']);
        Route::get('get_account_data.php', [AccountDataController::class, 'index']);
        Route::post('account_edit_process.php', [AccountDataController::class, 'update']);
        Route::post('account_delete_process.php', [AccountDataController::class, 'destroy']);
    });
});
