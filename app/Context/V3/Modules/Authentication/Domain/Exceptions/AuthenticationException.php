<?php

namespace App\Context\V3\Modules\Authentication\Domain\Exceptions;

use RuntimeException;

final class AuthenticationException extends RuntimeException
{
    /** @param array<string, string[]> $fieldErrors */
    public function __construct(
        string $message,
        public readonly string $errorCode = 'authentication_failed',
        public readonly int $status = 401,
        public readonly array $fieldErrors = [],
    ) {
        parent::__construct($message, $status);
    }
}
