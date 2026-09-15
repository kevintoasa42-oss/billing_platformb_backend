<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Exceptions;

use RuntimeException;

/**
 * Domain-level exception for the ThirdParty bounded context.
 *
 * Renders as an RFC 7807 problem+json response when caught by the bootstrap
 * exception handler.
 */
class ThirdPartyException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 422,
        public readonly string $errorCode = 'third_party_error',
        public readonly array $fieldErrors = [],
    ) {
        parent::__construct($message, $status);
    }
}
