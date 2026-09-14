<?php

namespace App\Context\V3\Modules\Core\Notification\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Notification\Application\DTOs\SendTestEmailDTO;
use App\Context\V3\Modules\Core\Notification\Application\Http\Requests\SendTestEmailRequest;
use App\Context\V3\Modules\Core\Notification\Application\UseCases\SendTestEmailUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class NotificationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SendTestEmailUseCase $useCase,
    ) {}

    public function sendTestEmail(SendTestEmailRequest $request): JsonResponse
    {
        $dto = SendTestEmailDTO::fromArray($request->validated());

        $sent = $this->useCase->execute($dto);

        if (! $sent) {
            return $this->error('Email could not be sent.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->success(['sent' => true], 'Test email sent.');
    }
}
