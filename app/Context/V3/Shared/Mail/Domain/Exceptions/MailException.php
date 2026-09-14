<?php

namespace App\Context\V3\Shared\Mail\Domain\Exceptions;

use RuntimeException;

class MailException extends RuntimeException
{
    public function __construct(string $message, int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function problemCode(): string
    {
        return 'mail_send_failed';
    }
}
