<?php

namespace App\Context\V3\Shared\Log\Infrastructure;

use Illuminate\Support\Facades\Log;

class PrettyLog
{
    public static function info(string $message, array $context = []): void
    {
        if (empty($context)) {
            Log::info($message);

            return;
        }

        $prettyContext = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        Log::info("$message \n$prettyContext");
    }

    public static function debug(string $message, array $context = []): void
    {
        if (empty($context)) {
            Log::debug($message);

            return;
        }

        $prettyContext = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        Log::debug("$message \n$prettyContext");
    }

    public static function warning(string $message, array $context = []): void
    {
        if (empty($context)) {
            Log::warning($message);

            return;
        }

        $prettyContext = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        Log::warning("$message \n$prettyContext");
    }

    public static function error(string $message, array $context = []): void
    {
        if (empty($context)) {
            Log::error($message);

            return;
        }

        $prettyContext = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        Log::error("$message \n$prettyContext");
    }
}
