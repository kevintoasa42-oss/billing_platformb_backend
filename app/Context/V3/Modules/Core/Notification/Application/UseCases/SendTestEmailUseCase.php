<?php

namespace App\Context\V3\Modules\Core\Notification\Application\UseCases;

use App\Context\V3\Modules\Core\Notification\Application\DTOs\SendTestEmailDTO;
use App\Context\V3\Shared\Mail\Application\DTOs\SendEmailDTO;
use App\Context\V3\Shared\Mail\Application\UseCases\SendEmailUseCase;

class SendTestEmailUseCase
{
    public function __construct(
        private readonly SendEmailUseCase $sendEmail,
    ) {}

    public function execute(SendTestEmailDTO $dto): bool
    {
        $sendEmailDTO = new SendEmailDTO(
            to: [$dto->to],
            subject: $dto->subject,
            body: $dto->body,
            isHtml: $dto->isHtml,
        );

        return $this->sendEmail->execute($sendEmailDTO);
    }
}
