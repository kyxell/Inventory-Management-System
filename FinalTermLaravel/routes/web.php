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
    Route::get('csrf-token', fn () => response()->json(['csrfToken' => csrf_token()]));
    Route::post('login_process.php', [AuthController::class, 'login']);
    Route::post('register_process.php', [AuthController::class, 'register']);
    Route::post('logout_process.php', [AuthController::class, 'logout'])->middleware('auth');
});

require __DIR__.'/products.php';
require __DIR__.'/categories.php';
require __DIR__.'/inventory.php';
require __DIR__.'/accounts.php';

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
