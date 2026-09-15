<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Domain\Services;

use DomainException;

/**
 * Deterministic settlement math for transport operations.
 *
 * Amounts cross the HTTP/database boundary as decimal strings. Keeping the
 * policy integer-cent based makes the accounting result independent of PHP's
 * binary floating-point implementation and easy to test without a database.
 */
final class SettlementPolicy
{
    /**
     * @return array{
     *   customer_gross:string,
     *   customer_credits:string,
     *   customer_net:string,
     *   partner_invoices:string,
     *   partner_credits:string,
     *   partner_net:string,
     *   allocated:string,
     *   pending:string,
     *   overbilled:string,
     *   status:string,
     *   blocked:bool,
     *   review_pending:bool
     * }
     */
    public static function evaluate(
        string $customerGross,
        string $customerCredits,
        string $partnerInvoices,
        string $partnerCredits,
        string $allocated,
        bool $reviewPending = false,
    ): array {
        $gross = self::cents($customerGross);
        $customerCredit = self::cents($customerCredits);
        $partnerInvoice = self::cents($partnerInvoices);
        $partnerCredit = self::cents($partnerCredits);
        $allocatedCents = self::cents($allocated);

        $customerNet = max(0, $gross - $customerCredit);
        $partnerNet = max(0, $partnerInvoice - $partnerCredit);
        $pending = max(0, $customerNet - $allocatedCents);
        $overbilled = max(0, $allocatedCents - $customerNet);
        $blocked = $reviewPending || $overbilled > 0 || $partnerNet > $customerNet;
        $status = $blocked
            ? 'blocked'
            : ($pending === 0 ? 'settled' : ($allocatedCents === 0 ? 'open' : 'partially_settled'));

        return [
            'customer_gross' => self::money($gross),
            'customer_credits' => self::money($customerCredit),
            'customer_net' => self::money($customerNet),
            'partner_invoices' => self::money($partnerInvoice),
            'partner_credits' => self::money($partnerCredit),
            'partner_net' => self::money($partnerNet),
            'allocated' => self::money($allocatedCents),
            'pending' => self::money($pending),
            'overbilled' => self::money($overbilled),
            'status' => $status,
            'blocked' => $blocked,
            'review_pending' => $reviewPending,
        ];
    }

    private static function cents(string $value): int
    {
        $value = trim($value);
        if (preg_match('/^(?:0|[1-9][0-9]*)(?:\.[0-9]{1,2})?$/D', $value) !== 1) {
            throw new DomainException('Importe contable inválido.');
        }
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '0');
        $fraction = str_pad($fraction, 2, '0');
        $cents = ((int) $whole * 100) + (int) substr($fraction, 0, 2);
        if ($cents < 0) {
            throw new DomainException('Importe contable inválido.');
        }

        return $cents;
    }

    private static function money(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
