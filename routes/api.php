<?php

use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\DocumentationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('api-docs', [DocumentationController::class, 'index'])->name('api.documentation');
    
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update.status');
    });
}); 