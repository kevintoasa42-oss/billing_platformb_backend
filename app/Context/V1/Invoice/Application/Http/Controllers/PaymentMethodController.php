<?php

namespace App\Context\V1\Invoice\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PaymentMethodController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/payment-methods
     * Lista los metodos de pago del SRI (catalogo central).
     */
    public function index(): JsonResponse
    {
        $methods = DB::connection('pgsql')
            ->table('sri_payment_methods')
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'description']);

        return $this->successResponse($methods);
    }
}
