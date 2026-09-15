<?php

declare(strict_types=1);

use App\Context\V3\Modules\Fiscal\InvoiceDraft\Application\Http\Controllers\InvoiceDraftController;
use Illuminate\Support\Facades\Route;

Route::post('invoice-drafts', [InvoiceDraftController::class, 'store']);
