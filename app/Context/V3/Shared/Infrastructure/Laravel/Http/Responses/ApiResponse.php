<?php

declare(strict_types=1);

namespace App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Standard V3 API response envelope.
 *
 * All V3 JSON responses follow the format:
 * {
 *   "status": true|false,
 *   "message": "",
 *   "data": ...
 * }
 */
trait ApiResponse
{
    /**
     * Success response with a single resource or raw data.
     *
     * @param  JsonResource|array<mixed>|null  $data
     */
    protected function success(JsonResource|array|null $data = null, string $message = '', int $status = 200): JsonResponse
    {
        if ($data instanceof JsonResource) {
            $data = $data->resolve(request());
        }

        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Error response.
     *
     * @param  array<mixed>|null  $data
     */
    protected function error(string $message, int $status = 400, ?array $data = null): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
