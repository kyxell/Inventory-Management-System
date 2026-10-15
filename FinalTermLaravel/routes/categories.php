<?php

use App\Http\Controllers\Api\InventoryDataController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/process')->middleware('auth')->group(function () {
    Route::get('categories', [InventoryDataController::class, 'index'])->name('categories.index');
    Route::resource('categories', InventoryDataController::class)->only(['index']);
});
