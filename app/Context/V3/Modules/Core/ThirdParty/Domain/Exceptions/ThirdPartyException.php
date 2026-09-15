<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Exceptions;

use RuntimeException;

final class ThirdPartyException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $errorCode = 'third_party_request_failed',
        public readonly int $status = 422,
    ) {
        parent::__construct($message, $status);
    }
}
