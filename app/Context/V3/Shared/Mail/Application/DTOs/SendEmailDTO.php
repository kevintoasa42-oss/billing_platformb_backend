<?php

namespace App\Context\V3\Shared\Mail\Application\DTOs;

class SendEmailDTO
{
    /**
     * @param  array<int, string>  $to
     * @param  array<int, string>  $cc
     * @param  array<int, string>  $bcc
     * @param  array<int, string>  $attachments
     */
    public function __construct(
        public readonly array $to,
        public readonly string $subject,
        public readonly string $body,
        public readonly bool $isHtml = false,
        public readonly ?string $from = null,
        public readonly ?string $fromName = null,
        public readonly array $cc = [],
        public readonly array $bcc = [],
        public readonly array $attachments = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $to = $data['to'] ?? [];
        $to = is_array($to) ? $to : [$to];

        $cc = $data['cc'] ?? [];
        $cc = is_array($cc) ? $cc : [$cc];

        $bcc = $data['bcc'] ?? [];
        $bcc = is_array($bcc) ? $bcc : [$bcc];

        $attachments = $data['attachments'] ?? [];
        $attachments = is_array($attachments) ? $attachments : [$attachments];

        return new self(
            to: array_map('strval', $to),
            subject: $data['subject'],
            body: $data['body'],
            isHtml: $data['is_html'] ?? false,
            from: $data['from'] ?? null,
            fromName: $data['from_name'] ?? null,
            cc: array_map('strval', $cc),
            bcc: array_map('strval', $bcc),
            attachments: array_map('strval', $attachments),
        );
    }

    public function toArray(): array
    {
        return [
            'to' => $this->to,
            'subject' => $this->subject,
            'body' => $this->body,
            'is_html' => $this->isHtml,
            'from' => $this->from,
            'from_name' => $this->fromName,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'attachments' => $this->attachments,
        ];
    }
}
