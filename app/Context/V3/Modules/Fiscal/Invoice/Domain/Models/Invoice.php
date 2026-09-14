<?php

namespace App\Context\V3\Modules\Fiscal\Invoice\Domain\Models;

class Invoice
{
    /**
     * @param  array<int, array<string, mixed>>  $details
     * @param  array<int, array<string, mixed>>  $payments
     * @param  array<int, array<string, mixed>>  $additionalInfo
     * @param  array<string, mixed>|null  $recipient
     * @param  array<string, mixed>|null  $issuer
     * @param  array<int, string>  $cancellationCapabilities
     */
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $uuid = null,
        public readonly ?string $documentNumber = null,
        public readonly ?string $documentType = null,
        public readonly ?string $issuedAt = null,
        public readonly ?string $fiscalStatus = null,
        public readonly ?string $sriStatus = null,
        public readonly ?string $authorizationNumber = null,
        public readonly ?string $authorizationDate = null,
        public readonly ?bool $notValidForSri = null,
        public readonly ?string $fiscalStatusSummary = null,
        public readonly ?string $establishmentCode = null,
        public readonly ?string $emissionPointCode = null,
        public readonly ?int $sequential = null,
        public readonly ?string $subtotal = null,
        public readonly ?string $tax = null,
        public readonly ?string $discount = null,
        public readonly ?string $total = null,
        public readonly ?array $recipient = null,
        public readonly ?array $issuer = null,
        public readonly array $details = [],
        public readonly array $payments = [],
        public readonly array $additionalInfo = [],
        public readonly array $cancellationCapabilities = [],
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'document_number' => $this->documentNumber,
            'document_type' => $this->documentType,
            'issued_at' => $this->issuedAt,
            'fiscal_status' => $this->fiscalStatus,
            'sri_status' => $this->sriStatus,
            'authorization_number' => $this->authorizationNumber,
            'authorization_date' => $this->authorizationDate,
            'not_valid_for_sri' => $this->notValidForSri ?? false,
            'fiscal_status_summary' => $this->fiscalStatusSummary,
            'establishment_code' => $this->establishmentCode,
            'emission_point_code' => $this->emissionPointCode,
            'sequential' => $this->sequential,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'total' => $this->total,
            'recipient' => $this->recipient,
            'issuer' => $this->issuer,
            'details' => array_map(fn (InvoiceLine $l) => $l->toArray(), $this->details),
            'payments' => array_map(fn (InvoicePayment $p) => $p->toArray(), $this->payments),
            'additional_info' => $this->additionalInfo,
            'cancellation_capabilities' => $this->cancellationCapabilities,
        ];
    }
}
