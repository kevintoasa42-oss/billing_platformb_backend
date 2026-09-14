<?php

namespace App\Context\V3\Shared\Mail\Infrastructure;

use App\Context\V3\Shared\Mail\Domain\Models\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly Email $email,
        private readonly string $senderAddress,
        private readonly string $senderName,
    ) {}

    public function build(): self
    {
        $message = $this->from($this->senderAddress, $this->senderName)
            ->subject($this->email->subject);

        if ($this->email->isHtml) {
            $message->html($this->email->body);
        } else {
            $message->html(nl2br(e($this->email->body)));
        }

        foreach ($this->email->attachments as $path) {
            $message->attach($path);
        }

        return $message;
    }
}
