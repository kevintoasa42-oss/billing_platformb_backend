<?php

namespace App\Context\V3\Modules\Core\Company\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Company\Application\DTOs\CompanyCreateDTO;
use App\Context\V3\Modules\Core\Company\Application\DTOs\CompanyUpdateDTO;
use App\Context\V3\Modules\Core\Company\Application\Http\Requests\CompanyCreateRequest;
use App\Context\V3\Modules\Core\Company\Application\Http\Requests\CompanyUpdateRequest;
use App\Context\V3\Modules\Core\Company\Application\UseCases\CompanyUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CompanyController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CompanyUseCase $useCase,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $companies = $this->useCase->all();

        return $this->success($companies, 'Companies loaded.');
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $company = $this->useCase->find($id);

        if ($company === null) {
            return $this->error('Company not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->success($company, 'Company loaded.');
    }

    public function store(CompanyCreateRequest $request): JsonResponse
    {
        $dto = CompanyCreateDTO::fromArray($request->validated());

        $company = $this->useCase->create($dto);

        return $this->success($company, 'Company created.', Response::HTTP_CREATED);
    }

    public function update(CompanyUpdateRequest $request, string $id): JsonResponse
    {
        $dto = CompanyUpdateDTO::fromArray($request->validated());

        $company = $this->useCase->update($id, $dto);

        if ($company === null) {
            return $this->error('Company not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->success($company, 'Company updated.');
    }
}
