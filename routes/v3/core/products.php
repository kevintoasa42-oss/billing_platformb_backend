<?php

use App\Context\V3\Modules\Core\Product\Application\Http\Controllers\ProductController;
use App\Context\V3\Modules\Core\Product\Application\Http\Controllers\ProductSettingsController;
use App\Context\V3\Modules\Core\Product\Application\Http\Controllers\ProductTaxController;
use Illuminate\Support\Facades\Route;

Route::get('products', [ProductController::class, 'index']);
Route::get('products/duplicates', [ProductController::class, 'duplicates']);
Route::post('products', [ProductController::class, 'store']);
Route::patch('products/{id}/status', [ProductController::class, 'setStatus'])->whereNumber('id');
Route::patch('products/{id}', [ProductController::class, 'update'])->whereNumber('id');
Route::delete('products/{id}', [ProductController::class, 'destroy'])->whereNumber('id');
Route::get('product-settings', [ProductSettingsController::class, 'index']);
Route::post('product-settings', [ProductSettingsController::class, 'update']);
Route::post('products/{product}/taxes', [ProductTaxController::class, 'store'])->whereNumber('product');
Route::delete('products/{product}/taxes/{tax}', [ProductTaxController::class, 'destroy'])->whereNumber(['product', 'tax']);
