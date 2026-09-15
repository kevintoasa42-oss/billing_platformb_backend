<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Mappers;

use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\Invoice;
use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\InvoiceLine;
use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\InvoicePayment;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models\DocumentModel;

/**
 * Maps a loaded DocumentModel (with lines and payments eager-loaded)
 * into a Domain Invoice. Performs no database queries.
 */
final class InvoiceMapper
{
    public function toDomain(DocumentModel $record): Invoice
    {
        $lines = $record->relationLoaded('lines')
            ? $record->lines->map(fn ($l): InvoiceLine => $this->mapLine($l))->all()
            : [];

        $payments = $record->relationLoaded('payments')
            ? $record->payments->map(fn ($p): InvoicePayment => $this->mapPayment($p))->all()
            : [];

        $recipient = is_array($record->recipient_snapshot) ? $record->recipient_snapshot : null;
        $issuer = is_array($record->issuer_snapshot) ? $record->issuer_snapshot : null;

        $documentNumber = $this->buildDocumentNumber(
            (string) $record->establishment_code,
            (string) $record->emission_point_code,
            (int) $record->sequential,
        );

        return new Invoice(
            id: isset($record->legacy_id) ? (int) $record->legacy_id : null,
            uuid: (string) $record->id,
            documentNumber: $documentNumber,
            documentType: (string) $record->document_type,
            issuedAt: $record->issued_at?->toIso8601String(),
            fiscalStatus: (string) $record->fiscal_status,
            sriStatus: $this->resolveSriStatus($record),
            authorizationNumber: $record->authorization_number,
            authorizationDate: null,
            notValidForSri: (bool) $record->not_valid_for_sri,
            fiscalStatusSummary: $record->fiscal_status_summary,
            establishmentCode: (string) $record->establishment_code,
            emissionPointCode: (string) $record->emission_point_code,
            sequential: (int) $record->sequential,
            subtotal: (string) $record->subtotal,
            tax: (string) $record->tax,
            discount: (string) $record->discount,
            total: (string) $record->total,
            recipient: $recipient,
            issuer: $issuer,
            details: $lines,
            payments: $payments,
            additionalInfo: [],
            cancellationCapabilities: [],
        );
    }

    private function mapLine(object $l): InvoiceLine
    {
        return new InvoiceLine(
            id: isset($l->legacy_id) ? (int) $l->legacy_id : null,
            productId: $l->product_id !== null ? (string) $l->product_id : null,
            productName: is_string($l->description_snapshot) ? $l->description_snapshot : null,
            sriPrincipalCode: $l->sri_principal_code ?? null,
            quantity: isset($l->quantity) ? (string) $l->quantity : null,
            unitPrice: isset($l->unit_price) ? (string) $l->unit_price : null,
            subtotal: isset($l->subtotal) ? (string) $l->subtotal : null,
            discount: isset($l->discount) ? (string) $l->discount : null,
            tax: isset($l->tax) ? (string) $l->tax : null,
            total: isset($l->total) ? (string) $l->total : null,
            invoiceDetailId: isset($l->legacy_id) ? (int) $l->legacy_id : null,
            taxes: [],
        );
    }

    private function mapPayment(object $p): InvoicePayment
    {
        return new InvoicePayment(
            id: isset($p->legacy_id) ? (int) $p->legacy_id : null,
            position: isset($p->position) ? (int) $p->position : null,
            paymentMethodCode: $p->payment_method_code ?? null,
            total: isset($p->total) ? (string) $p->total : null,
            term: $p->term ?? null,
            timeUnit: $p->time_unit ?? null,
            dueDate: $p->due_date ?? null,
        );
    }

    private function buildDocumentNumber(string $establishmentCode, string $emissionPointCode, int $sequential): string
    {
        return $establishmentCode.'-'.$emissionPointCode.'-'.str_pad((string) $sequential, 9, '0', STR_PAD_LEFT);
    }

    private function resolveSriStatus(DocumentModel $record): string
    {
        if (! empty($record->legacy_sri_status)) {
            return (string) $record->legacy_sri_status;
        }

        return match ((string) $record->fiscal_status) {
            'authorized' => 'AUTHORIZED',
            'simulated' => 'PENDING',
            'voided' => 'VOIDED',
            'rejected' => 'REJECTED',
            default => 'PENDING',
        };
    }
}
