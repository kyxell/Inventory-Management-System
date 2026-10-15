<?php

use App\Http\Controllers\Api\InventoryDataController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/process')->middleware('auth')->group(function () {
    Route::get('inventory', [InventoryDataController::class, 'index'])->name('inventory.index');
    Route::get('get_data.php', [InventoryDataController::class, 'index'])->name('inventory.get_data');
    Route::get('inventory_summary', [InventoryDataController::class, 'index'])->name('inventory.summary');
});
