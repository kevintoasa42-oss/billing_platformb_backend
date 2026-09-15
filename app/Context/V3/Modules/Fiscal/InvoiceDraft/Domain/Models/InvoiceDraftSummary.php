<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\InvoiceDraft\Domain\Models;

final class InvoiceDraftSummary
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array{client_name: string, product_count: int, total: string, age_seconds: int}
     */
    public static function fromPayload(array $payload, int $ageSeconds = 0): array
    {
        $client = is_array($payload['client'] ?? null) ? $payload['client'] : [];
        $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
        $subtotal = 0.0;
        $tax = 0.0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $line = max(0, (float) ($item['quantity'] ?? 0))
                * max(0, (float) ($item['unitPrice'] ?? 0));
            $subtotal += $line;
            $tax += $line * max(0, (float) ($item['taxRate'] ?? 0)) / 100;
        }

        $discount = min($subtotal, max(0, (float) ($payload['discount'] ?? 0)));
        $taxAfterDiscount = $subtotal > 0 ? $tax * (($subtotal - $discount) / $subtotal) : 0;

        return [
            'client_name' => trim((string) ($client['name'] ?? 'Sin cliente')) ?: 'Sin cliente',
            'product_count' => count($items),
            'total' => number_format(max(0, $subtotal - $discount + $taxAfterDiscount), 2, '.', ''),
            'age_seconds' => max(0, $ageSeconds),
        ];
    }
}
