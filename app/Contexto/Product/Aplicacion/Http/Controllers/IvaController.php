<?php

namespace App\Contexto\Product\Aplicacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class IvaController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/ivas
     * Lista los tipos de IVA del SRI (catálogo central).
     */
    public function index(): JsonResponse
    {
        $ivas = DB::connection('pgsql')
            ->table('sri_iva_percentages')
            ->orderBy('porcentaje', 'desc')
            ->get(['id', 'codigo', 'nombre', 'porcentaje', 'descripcion']);

        return $this->successResponse($ivas);
    }
}
