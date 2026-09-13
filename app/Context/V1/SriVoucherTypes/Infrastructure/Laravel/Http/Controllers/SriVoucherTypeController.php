<?php

namespace App\Context\V1\SriVoucherTypes\Infrastructure\Laravel\Http\Controllers;

use App\Context\V1\SriVoucherTypes\Application\DTOs\SriVoucherTypeDTO;
use App\Context\V1\SriVoucherTypes\Application\UseCases\SriVoucherTypeCrudService;
use App\Context\V1\SriVoucherTypes\Domain\Exceptions\SriVoucherTypeNotFoundException;
use App\Context\V1\SriVoucherTypes\Infrastructure\Laravel\Http\Requests\CreateSriVoucherTypeRequest;
use App\Context\V1\SriVoucherTypes\Infrastructure\Laravel\Http\Requests\UpdateSriVoucherTypeRequest;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SriVoucherTypeController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly SriVoucherTypeCrudService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = array_filter([
            'search' => $request->query('search'),
            'code' => $request->query('code'),
            'retention' => $request->has('retention') ? $request->boolean('retention') : null,
            'current' => $request->has('current') ? $request->boolean('current') : null,
        ], static fn ($value) => $value !== null && $value !== '');

        return $this->successResponse($this->service->list(
            max(1, (int) $request->query('page', 1)),
            min(100, max(1, (int) $request->query('perPage', 15))),
            $filters,
        ));
    }

    public function show(int $id): JsonResponse
    {
        $voucherType = $this->service->get($id);

        return $voucherType
            ? $this->successResponse($voucherType->toArray())
            : $this->errorResponse('SRI voucher type not found.', 404);
    }

    public function store(CreateSriVoucherTypeRequest $request): JsonResponse
    {
        return $this->successResponse(
            $this->service->create(SriVoucherTypeDTO::fromArray($request->validated()))->toArray(),
            201,
        );
    }

    public function update(int $id, UpdateSriVoucherTypeRequest $request): JsonResponse
    {
        try {
            return $this->successResponse($this->service->update(
                SriVoucherTypeDTO::fromArray([...$request->validated(), 'id' => $id]),
            )->toArray());
        } catch (SriVoucherTypeNotFoundException) {
            return $this->errorResponse('SRI voucher type not found.', 404);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->service->delete($id)
            ? $this->successResponse('SRI voucher type deleted successfully.')
            : $this->errorResponse('SRI voucher type not found.', 404);
    }
}
