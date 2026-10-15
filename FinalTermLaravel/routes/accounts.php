<?php

use App\Http\Controllers\Api\AccountDataController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/process')->middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('accounts', [AccountDataController::class, 'index'])->name('accounts.index');
    Route::apiResource('accounts', AccountDataController::class)->only(['index', 'update', 'destroy']);

    Route::get('get_account_data.php', [AccountDataController::class, 'index'])->name('legacy.accounts.index');
    Route::post('account_edit_process.php', [AccountDataController::class, 'update'])->name('legacy.accounts.update');
    Route::post('account_delete_process.php', [AccountDataController::class, 'destroy'])->name('legacy.accounts.destroy');
});
