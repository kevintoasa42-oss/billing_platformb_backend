<?php

namespace App\Context\V1\Modules\Clients\Infrastructure\Laravel\Http\Controllers;

use App\Context\V1\Modules\Clients\Application\DTOs\ClientDTO;
use App\Context\V1\Modules\Clients\Application\UseCases\ClientCrudService;
use App\Context\V1\Modules\Clients\Domain\Exceptions\ClientNotFoundException;
use App\Context\V1\Modules\Clients\Infrastructure\Laravel\Http\Requests\CreateClientRequest;
use App\Context\V1\Modules\Clients\Infrastructure\Laravel\Http\Requests\UpdateClientRequest;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ClientController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ClientCrudService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = array_filter($request->only(['search', 'status', 'type', 'identification_type']), static fn($value) => $value !== null && $value !== '');

        return $this->successResponse($this->service->list(max(1, (int)$request->query('page', 1)), min(100, max(1, (int)$request->query('perPage', 15))), $filters));
    }

    public function show(int $id): JsonResponse
    {
        $client = $this->service->get($id);

        return $client ? $this->successResponse($client->toArray()) : $this->errorResponse('Client not found.', 404);
    }

    public function store(CreateClientRequest $request): JsonResponse
    {
        return $this->successResponse($this->service->create(ClientDTO::fromArray($request->validated()))->toArray(), 201);
    }

    public function update(int $id, UpdateClientRequest $request): JsonResponse
    {
        try {
            return $this->successResponse($this->service->update(ClientDTO::fromArray([...$request->validated(), 'id' => $id]))->toArray());
        } catch (ClientNotFoundException) {
            return $this->errorResponse('Client not found.', 404);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->service->delete($id)
            ? $this->successResponse('Client deleted successfully.')
            : $this->errorResponse('Client not found.', 404);
    }
}
