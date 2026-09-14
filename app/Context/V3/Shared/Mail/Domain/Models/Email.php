<?php

namespace App\Context\V3\Shared\Mail\Domain\Models;

/**
 * Pure domain model for an email message.
 */
class Email
{
    /**
     * @param  array<int, EmailRecipient>  $recipients
     * @param  array<int, EmailRecipient>  $cc
     * @param  array<int, EmailRecipient>  $bcc
     * @param  array<int, string>  $attachments
     */
    public function __construct(
        public readonly string $subject,
        public readonly string $body,
        public readonly array $recipients,
        public readonly ?string $from = null,
        public readonly ?string $fromName = null,
        public readonly array $cc = [],
        public readonly array $bcc = [],
        public readonly bool $isHtml = false,
        public readonly array $attachments = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $recipients = array_map(
            static fn (array $r): EmailRecipient => EmailRecipient::fromArray($r),
            $data['recipients'] ?? [],
        );
        $cc = array_map(
            static fn (array $r): EmailRecipient => EmailRecipient::fromArray($r),
            $data['cc'] ?? [],
        );
        $bcc = array_map(
            static fn (array $r): EmailRecipient => EmailRecipient::fromArray($r),
            $data['bcc'] ?? [],
        );

        return new self(
            subject: $data['subject'],
            body: $data['body'],
            recipients: $recipients,
            from: $data['from'] ?? null,
            fromName: $data['from_name'] ?? null,
            cc: $cc,
            bcc: $bcc,
            isHtml: $data['is_html'] ?? false,
            attachments: $data['attachments'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'subject' => $this->subject,
            'body' => $this->body,
            'recipients' => array_map(fn (EmailRecipient $r): array => $r->toArray(), $this->recipients),
            'from' => $this->from,
            'from_name' => $this->fromName,
            'cc' => array_map(fn (EmailRecipient $r): array => $r->toArray(), $this->cc),
            'bcc' => array_map(fn (EmailRecipient $r): array => $r->toArray(), $this->bcc),
            'is_html' => $this->isHtml,
            'attachments' => $this->attachments,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function recipientAddresses(): array
    {
        return array_map(fn (EmailRecipient $r): string => $r->address, $this->recipients);
    }

    /**
     * @return array<int, string>
     */
    public function ccAddresses(): array
    {
        return array_map(fn (EmailRecipient $r): string => $r->address, $this->cc);
    }

    /**
     * @return array<int, string>
     */
    public function bccAddresses(): array
    {
        return array_map(fn (EmailRecipient $r): string => $r->address, $this->bcc);
    }
}
