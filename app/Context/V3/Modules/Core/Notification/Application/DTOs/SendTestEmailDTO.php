<?php

namespace App\Context\V3\Modules\Core\Notification\Application\DTOs;

class SendTestEmailDTO
{
    public function __construct(
        public readonly string $to,
        public readonly string $subject,
        public readonly string $body,
        public readonly bool $isHtml = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            to: $data['to'],
            subject: $data['subject'],
            body: $data['body'],
            isHtml: $data['is_html'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'to' => $this->to,
            'subject' => $this->subject,
            'body' => $this->body,
            'is_html' => $this->isHtml,
        ];
    }
}
