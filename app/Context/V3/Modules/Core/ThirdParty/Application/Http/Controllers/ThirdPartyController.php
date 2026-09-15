<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\Http\Controllers;

use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyCreateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests\CreateThirdPartyRequest;
use App\Context\V3\Modules\Core\ThirdParty\Application\UseCases\CreateThirdPartyUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class ThirdPartyController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CreateThirdPartyUseCase $useCase,
    ) {}

    public function store(CreateThirdPartyRequest $request): JsonResponse
    {
        $data = $this->useCase->create(
            ThirdPartyCreateDTO::fromArray($request->validated()),
        );

        return $this->success($data, 'Tercero creado.', 201);
    }
}
