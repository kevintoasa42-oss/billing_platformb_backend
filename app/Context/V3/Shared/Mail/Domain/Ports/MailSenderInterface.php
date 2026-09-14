<?php

namespace App\Context\V3\Shared\Mail\Domain\Ports;

use App\Context\V3\Shared\Mail\Domain\Exceptions\MailException;
use App\Context\V3\Shared\Mail\Domain\Models\Email;

interface MailSenderInterface
{
    /**
     * @throws MailException
     */
    public function send(Email $email): bool;
}
