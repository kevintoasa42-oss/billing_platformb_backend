<?php

declare(strict_types=1);

namespace App\Context\V3\Shared\Infrastructure\Postgres;

/**
 * Normalizes user-friendly daterange inputs into PostgreSQL daterange literals.
 *
 * Accepts "start/end" (e.g. "2026-01-01/2026-12-31") or "start" (open-ended)
 * and converts them to the Postgres range literal form "[start,end)".
 * Already-normalized values (starting with "[" or "(") are passed through.
 */
final class DaterangeNormalizer
{
    /**
     * @param string|null $validity User input or already-normalized range.
     * @return string|null Postgres daterange literal like "[2026-01-01,2026-12-31)" or null.
     */
    public static function normalize(?string $validity): ?string
    {
        if ($validity === null || $validity === '') {
            return $validity;
        }

        // Already in Postgres range literal form — pass through as-is.
        if (str_starts_with($validity, '[') || str_starts_with($validity, '(')) {
            return $validity;
        }

        // Split on "/" — "start/end" or just "start" (open-ended).
        $parts = explode('/', $validity, 2);
        $start = trim($parts[0]);
        $end = isset($parts[1]) ? trim($parts[1]) : '';

        if ($end === '') {
            return "[{$start},)";
        }

        return "[{$start},{$end})";
    }
}
