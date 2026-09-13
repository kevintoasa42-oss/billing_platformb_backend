<?php

namespace App\Context\Product\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TaxController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/taxes
     * Lista los tipos de IVA del SRI (catálogo central).
     */
    public function index(): JsonResponse
    {
        $taxes = DB::connection('pgsql')
            ->table('sri_iva_percentages')
            ->orderBy('percentage', 'desc')
            ->get(['id', 'code', 'name', 'percentage', 'description']);

        return $this->successResponse($taxes);
    }
}
