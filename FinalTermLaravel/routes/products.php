<?php

use App\Http\Controllers\Api\InventoryActionController;
use App\Http\Controllers\Api\InventoryDataController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/process')->middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('products', [InventoryDataController::class, 'index'])->name('products.index');
    Route::apiResource('products', InventoryActionController::class)->only(['store', 'update', 'destroy']);

    Route::post('add_process.php', [InventoryActionController::class, 'store'])->name('legacy.products.store');
    Route::post('edit_process.php', [InventoryActionController::class, 'update'])->name('legacy.products.update');
    Route::post('delete_process.php', [InventoryActionController::class, 'destroy'])->name('legacy.products.destroy');
    Route::post('import_process.php', [InventoryActionController::class, 'import'])->name('legacy.products.import');
    Route::post('deliver_process.php', [InventoryActionController::class, 'deliver'])->name('legacy.products.deliver');
});
