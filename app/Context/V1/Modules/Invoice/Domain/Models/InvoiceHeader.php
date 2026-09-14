<?php

namespace App\Context\V1\Modules\Invoice\Domain\Models;

class InvoiceHeader
{
    public function __construct(
        public ?int $id = null,
        public ?int $carrier_id = null,
        public ?int $branch_office_id = null,
        public ?int $emission_point_id = null,
        // infoTributaria (issuer snapshot)
        public ?string $environment = '1',
        public ?string $emission_type = '1',
        public ?string $ruc = null,
        public ?string $legal_name = null,
        public ?string $tradename = null,
        public ?string $access_key = null,
        public ?string $document_code = '01',
        public ?string $establishment = null,
        public ?string $emission_point = null,
        public ?string $sequential = null,
        public ?string $matrix_address = null,
        // infoFactura (buyer snapshot + totals)
        public ?string $issue_date = null,
        public ?string $establishment_address = null,
        public string $accounting_required = 'NO',
        public ?string $buyer_identification_type = null,
        public ?string $buyer_name = null,
        public ?string $buyer_identification = null,
        public ?string $buyer_address = null,
        public ?string $buyer_phone = null,
        public ?string $buyer_email = null,
        public float $subtotal = 0,
        public float $discount = 0,
        public float $tax_base = 0,
        public float $tax = 0,
        public float $tip = 0,
        public float $total = 0,
        public string $currency = 'DOLAR',
        public ?string $plate = null,
        public string $status = 'PENDIENTE',
        /** @var InvoiceItem[] */
        public array $items = [],
        /** @var InvoiceTax[] */
        public array $taxes = [],
        /** @var InvoicePayment[] */
        public array $payments = [],
        /** @var InvoiceAdditionalInfo[] */
        public array $additional_info = [],
    ) {}
}
