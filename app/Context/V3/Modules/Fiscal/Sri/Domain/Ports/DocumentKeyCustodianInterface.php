<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Domain\Ports;

/**
 * Contract for key custody via a Hardware Security Module (OpenBao Transit).
 * The private key never leaves the custodian; only data is sent for signing.
 */
interface DocumentKeyCustodianInterface
{
    /**
     * Sign data with a named key reference.
     *
     * @param  string  $reference  The key reference name in the custodian.
     * @param  string  $data  The raw bytes to sign.
     * @param  int|null  $version  The key version to use (null = current).
     * @return array{reference: string, version: int, signature: string}
     */
    public function sign(string $reference, string $data, ?int $version = null): array;
}
