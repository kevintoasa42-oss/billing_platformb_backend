<?php

namespace App\Context\V3\Modules\Fiscal\Invoice\Domain\Models;

class InvoiceLine
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $productId = null,
        public readonly ?string $productName = null,
        public readonly ?string $sriPrincipalCode = null,
        public readonly ?string $quantity = null,
        public readonly ?string $unitPrice = null,
        public readonly ?string $subtotal = null,
        public readonly ?string $discount = null,
        public readonly ?string $tax = null,
        public readonly ?string $total = null,
        public readonly ?int $invoiceDetailId = null,
        public readonly array $taxes = [],
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'sri_principal_code' => $this->sriPrincipalCode,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount ?? '0.00',
            'tax' => $this->tax ?? '0.00',
            'total' => $this->total ?? $this->subtotal,
            'invoice_detail_id' => $this->invoiceDetailId,
            'taxes' => $this->taxes,
        ];
    }
}
