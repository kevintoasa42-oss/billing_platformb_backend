<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http;

use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;
use Illuminate\Contracts\Container\Container;

/**
 * Accesses the session that AuthenticateV3SessionCookie already authenticated
 * and registered for the current request.
 */
final readonly class CurrentAuthenticationSession
{
    public function __construct(private Container $container) {}

    public function get(): AuthenticationSession
    {
        return $this->container->make(AuthenticationSession::class);
    }
}
