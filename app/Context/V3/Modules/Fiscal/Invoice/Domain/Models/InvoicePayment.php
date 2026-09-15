<?php

namespace App\Context\V3\Modules\Fiscal\Invoice\Domain\Models;

class InvoicePayment
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?int $position = null,
        public readonly ?string $paymentMethodCode = null,
        public readonly ?string $total = null,
        public readonly ?int $term = null,
        public readonly ?string $timeUnit = null,
        public readonly ?string $dueDate = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'payment_method_code' => $this->paymentMethodCode,
            'total' => $this->total,
            'term' => $this->term,
            'time_unit' => $this->timeUnit,
            'due_date' => $this->dueDate,
        ];
    }
}
