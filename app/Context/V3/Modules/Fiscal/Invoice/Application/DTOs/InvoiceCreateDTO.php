<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Application\DTOs;

final class InvoiceCreateDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @param  array<int, array<string, mixed>>  $payments
     * @param  array<string, mixed>  $recipient
     * @param  array<string, mixed>  $issuer
     */
    public function __construct(
        public readonly string $emissionPointId,
        public readonly string $establishmentCode,
        public readonly string $emissionPointCode,
        public readonly array $recipient,
        public readonly array $issuer,
        public readonly array $lines,
        public readonly array $payments,
        public readonly string $subtotal,
        public readonly string $tax,
        public readonly string $total,
        public readonly ?string $discount = null,
        public readonly ?string $dueAt = null,
        public readonly ?string $idempotencyKey = null,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public static function fromArray(array $input): self
    {
        return new self(
            emissionPointId: (string) $input['emission_point_id'],
            establishmentCode: (string) $input['establishment_code'],
            emissionPointCode: (string) $input['emission_point_code'],
            recipient: is_array($input['recipient'] ?? null) ? $input['recipient'] : [],
            issuer: is_array($input['issuer'] ?? null) ? $input['issuer'] : [],
            lines: is_array($input['lines'] ?? null) ? $input['lines'] : [],
            payments: is_array($input['payments'] ?? null) ? $input['payments'] : [],
            subtotal: (string) $input['subtotal'],
            tax: (string) $input['tax'],
            total: (string) $input['total'],
            discount: isset($input['discount']) ? (string) $input['discount'] : null,
            dueAt: $input['due_at'] ?? null,
            idempotencyKey: $input['idempotency_key'] ?? null,
        );
    }
}
