<?php

use App\Context\V3\Modules\Core\Product\Application\Http\Controllers\ProductController;
use App\Context\V3\Modules\Core\Product\Application\Http\Controllers\ProductSettingsController;
use App\Context\V3\Modules\Core\SriIva\Application\Http\Controllers\SriIvaTypeController;
use Illuminate\Support\Facades\Route;

/*
 * Root catalog endpoints retained for the legacy V3 frontend contract.
 * Their implementation remains in the Core bounded context.
 */
Route::get('sri-iva-types', [SriIvaTypeController::class, 'index']);
Route::get('product-settings', [ProductSettingsController::class, 'index']);
Route::get('products', [ProductController::class, 'index']);
