<?php

namespace App\Context\V1\XmlGeneration\Application\Http\Controllers;

use App\Context\V1\XmlGeneration\Application\UseCases\GenerateInvoiceXmlUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class InvoiceXmlController extends Controller
{
    public function __construct(
        private GenerateInvoiceXmlUseCase $generateXmlUseCase,
    ) {}

    /**
     * GET /api/invoices/{id}/xml
     * Returns the SRI factura XML for the given invoice.
     */
    public function show(int $id): Response|JsonResponse
    {
        $xml = $this->generateXmlUseCase->execute($id);

        if (!$xml) {
            return response()->json([
                'status' => false,
                'response' => ['message' => 'Invoice not found.'],
            ], 404);
        }

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
