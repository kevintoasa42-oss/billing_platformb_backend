<?php

declare(strict_types=1);

use App\Context\V3\Modules\Fiscal\Invoice\Application\Http\Controllers\InvoiceController;
use App\Context\V3\Modules\Fiscal\Invoice\Application\Http\Controllers\InvoiceDraftController;
use Illuminate\Support\Facades\Route;

Route::prefix('invoice-drafts')->group(function (): void {
    Route::get('/', [InvoiceDraftController::class, 'index']);
    Route::post('/', [InvoiceDraftController::class, 'save']);
    Route::get('current', [InvoiceDraftController::class, 'current']);
    Route::put('current', [InvoiceDraftController::class, 'save']);
    Route::delete('current', [InvoiceDraftController::class, 'delete']);
    Route::put('{publicId}', [InvoiceDraftController::class, 'save']);
    Route::delete('{publicId}', [InvoiceDraftController::class, 'delete']);
    Route::options('{publicId}', [InvoiceDraftController::class, 'options']);
});

Route::prefix('invoices')->group(function (): void {
    // Static routes first to avoid collision with /{id}
    Route::get('payment-methods', [InvoiceController::class, 'paymentMethods']);
    Route::get('readiness', [InvoiceController::class, 'readiness']);
    Route::get('summary', [InvoiceController::class, 'summary']);
    Route::get('export', [InvoiceController::class, 'export']);

    Route::get('/', [InvoiceController::class, 'index']);
    Route::post('/', [InvoiceController::class, 'store']);

    // Dynamic routes with {id} (numeric)
    Route::get('{id}/sri-status', [InvoiceController::class, 'sriStatus'])->whereNumber('id');
    Route::get('{id}/cancellation-workflows', [InvoiceController::class, 'cancellationWorkflow'])->whereNumber('id');
    Route::post('{id}/cancellation-workflows/verify', [InvoiceController::class, 'verifyCancellationWorkflow'])->whereNumber('id');
    Route::post('{id}/sri-submissions', [InvoiceController::class, 'authorize'])->whereNumber('id');
    Route::get('{id}/xml', [InvoiceController::class, 'xmlArtifact'])->whereNumber('id');
    Route::get('{id}/signed-xml', [InvoiceController::class, 'signedXmlArtifact'])->whereNumber('id');
    Route::get('{id}/ride', [InvoiceController::class, 'rideArtifact'])->whereNumber('id');
    Route::get('{id}', [InvoiceController::class, 'show'])->whereNumber('id');
    Route::delete('{id}', [InvoiceController::class, 'void'])->whereNumber('id');
});
