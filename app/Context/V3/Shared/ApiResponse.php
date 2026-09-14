<?php

namespace App\Context\V3\Shared;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * Standard API response envelope.
 *
 * All JSON responses follow the format:
 * {
 *   "status": true|false,
 *   "message": "",
 *   "data": ...
 * }
 *
 * Paginated responses also include "meta" and "links".
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
     * Collection response (paginated or non-paginated).
     *
     * Preserves pagination "meta" and "links" when present.
     */
    /**
     * @param  array<string, mixed>  $extraMeta
     */
    protected function collection(AnonymousResourceCollection $collection, string $message = '', array $extraMeta = []): JsonResponse
    {
        $response = $collection->toResponse(request());
        $original = $response->getData(true);

        $payload = [
            'status' => true,
            'message' => $message,
            'data' => $original['data'] ?? $original,
        ];

        if (isset($original['meta'])) {
            $payload['meta'] = array_merge($original['meta'], $extraMeta);
        } elseif ($extraMeta !== []) {
            $payload['meta'] = $extraMeta;
        }

        if (isset($original['links'])) {
            $payload['links'] = $original['links'];
        }

        return response()->json($payload, $response->getStatusCode());
    }

    /**
     * Error response.
     *
     * @param  array<mixed>|null  $data
     */
    protected function error(string $message, int $status = 400, ?array $data = null): JsonResponse
    {
        if (request()->is('api/v3/*') || request()->is('api/v3/*')) {
            $requestId = request()->header('X-Request-Id') ?: (string) Str::uuid();
            $code = is_array($data) && isset($data['code']) && is_string($data['code'])
                ? $data['code']
                : ($status === 404 ? 'not_found' : ($status === 403 ? 'forbidden' : 'request_failed'));
            $fieldErrors = is_array($data) && isset($data['fieldErrors']) && is_array($data['fieldErrors'])
                ? $data['fieldErrors']
                : [];

            return response()->json([
                'type' => 'https://artra.cloud/problems/'.$code,
                'title' => $message,
                'status' => $status,
                'code' => $code,
                'message' => $message,
                'fieldErrors' => $fieldErrors,
                'requestId' => $requestId,
            ], $status, [
                'Content-Type' => 'application/problem+json',
                'X-Request-Id' => $requestId,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
