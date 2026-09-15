<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\Http\Controllers;

use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests\ThirdPartyAvailabilityRequest;
use App\Context\V3\Modules\Core\ThirdParty\Application\UseCases\ThirdPartyAvailabilityUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class ThirdPartyAvailabilityController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ThirdPartyAvailabilityUseCase $useCase,
    ) {}

    public function __invoke(ThirdPartyAvailabilityRequest $request): JsonResponse
    {
        $data = $this->useCase->execute(
            (string) $request->validated('identification', ''),
            $request->validated('identification_type'),
            $request->validated('exclude_id'),
        );

        return $this->success($data, 'Disponibilidad de identificación verificada.');
    }
}
