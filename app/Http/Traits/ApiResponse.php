<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Trait para estandarizar todas las respuestas del API.
 * Formato: { status: bool, response: mixed }
 */
trait ApiResponse
{
    /**
     * Respuesta exitosa.
     *
     * @param  mixed  $data
     * @param  int  $httpStatus
     * @return JsonResponse
     */
    protected function successResponse(mixed $data, int $httpStatus = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'response' => $data,
        ], $httpStatus);
    }

    /**
     * Respuesta de error.
     *
     * @param  string  $message
     * @param  int  $httpStatus
     * @return JsonResponse
     */
    protected function errorResponse(string $message, int $httpStatus = 400): JsonResponse
    {
        return response()->json([
            'status' => false,
            'response' => $message,
        ], $httpStatus);
    }
}
