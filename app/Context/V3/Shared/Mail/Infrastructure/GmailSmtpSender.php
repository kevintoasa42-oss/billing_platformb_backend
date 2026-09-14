<?php

namespace App\Context\V3\Shared\Mail\Infrastructure;

use App\Context\V3\Shared\Mail\Domain\Exceptions\MailException;
use App\Context\V3\Shared\Mail\Domain\Models\Email;
use App\Context\V3\Shared\Mail\Domain\Ports\MailSenderInterface;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;

class GmailSmtpSender implements MailSenderInterface
{
    public function send(Email $email): bool
    {
        $from = $email->from ?? (string) config('mail.from.address');
        $fromName = $email->fromName ?? (string) config('mail.from.name');

        try {
            $message = Mail::to($email->recipientAddresses());

            if ($email->ccAddresses() !== []) {
                $message->cc($email->ccAddresses());
            }
            if ($email->bccAddresses() !== []) {
                $message->bcc($email->bccAddresses());
            }

            $message->send(new GenericMailable($email, $from, $fromName));

            return true;
        } catch (TransportException $e) {
            throw new MailException(
                'Could not send email: SMTP transport error. '.$e->getMessage(),
                500,
                $e,
            );
        } catch (\Throwable $e) {
            throw new MailException(
                'Could not send email: '.$e->getMessage(),
                500,
                $e,
            );
        }
    }
}
