<?php

namespace App\Context\V3\Shared\Mail\Application\UseCases;

use App\Context\V3\Shared\Log\Infrastructure\PrettyLog;
use App\Context\V3\Shared\Mail\Application\DTOs\SendEmailDTO;
use App\Context\V3\Shared\Mail\Domain\Models\Email;
use App\Context\V3\Shared\Mail\Domain\Models\EmailRecipient;
use App\Context\V3\Shared\Mail\Domain\Ports\MailSenderInterface;

class SendEmailUseCase
{
    public function __construct(
        private readonly MailSenderInterface $sender,
    ) {}

    public function execute(SendEmailDTO $dto): bool
    {
        $recipients = array_map(
            static fn (string $address): EmailRecipient => new EmailRecipient($address),
            $dto->to,
        );
        $cc = array_map(
            static fn (string $address): EmailRecipient => new EmailRecipient($address),
            $dto->cc,
        );
        $bcc = array_map(
            static fn (string $address): EmailRecipient => new EmailRecipient($address),
            $dto->bcc,
        );

        $email = new Email(
            subject: $dto->subject,
            body: $dto->body,
            recipients: $recipients,
            from: $dto->from,
            fromName: $dto->fromName,
            cc: $cc,
            bcc: $bcc,
            isHtml: $dto->isHtml,
            attachments: $dto->attachments,
        );

        PrettyLog::info('Sending email', [
            'to' => $dto->to,
            'subject' => $dto->subject,
            'is_html' => $dto->isHtml,
        ]);

        return $this->sender->send($email);
    }
}
