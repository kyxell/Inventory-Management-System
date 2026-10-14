<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\AccountDataController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InventoryActionController;
use App\Http\Controllers\Api\InventoryDataController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/stock');

Route::prefix('api/process')->group(function () {
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

Route::view('/stock/{path?}', 'app')
    ->where('path', '.*')
    ->name('stock');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
