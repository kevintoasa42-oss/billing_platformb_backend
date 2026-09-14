<?php

namespace App\Context\V3\Modules\Core\Notification;

use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindings are auto-resolved by the container through MailServiceProvider.
    }
}
