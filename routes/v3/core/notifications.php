<?php

use App\Context\V3\Modules\Core\Notification\Application\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/**
 * Notifications — NotificationController backed by SendTestEmailUseCase.
 * Delegates to SendEmailUseCase + MailSenderInterface (GmailSmtpSender via MailServiceProvider).
 * Uses Laravel Mail facade (defaults to 'log' mailer in dev).
 */
Route::prefix('notifications')->group(function (): void {
    Route::post('/test-email', [NotificationController::class, 'sendTestEmail']);
});
