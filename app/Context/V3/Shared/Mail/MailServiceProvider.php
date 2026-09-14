<?php

namespace App\Context\V3\Shared\Mail;

use App\Context\V3\Shared\Mail\Domain\Ports\MailSenderInterface;
use App\Context\V3\Shared\Mail\Infrastructure\GmailSmtpSender;
use Illuminate\Support\ServiceProvider;

class MailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MailSenderInterface::class, GmailSmtpSender::class);
    }
}
