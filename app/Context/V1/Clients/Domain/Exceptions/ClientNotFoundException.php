<?php

namespace App\Context\V1\Clients\Domain\Exceptions;

use RuntimeException;

final class ClientNotFoundException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Client {$id} was not found.");
    }
}
