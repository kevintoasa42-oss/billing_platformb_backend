<?php

declare(strict_types=1);

namespace App\Context\V3\Shared;

use App\Contexts\IAM\Domain\Policies\CapabilityCatalog;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

trait AuthorizesConsolidatedCapability
{
    protected function authorizeCapability(Request $request, string $capability): void
    {
        $session = $request->attributes->get('consolidated.session');
        $granted = is_array($session) ? (array) ($session['capabilities'] ?? []) : [];
        if (CapabilityCatalog::allows($granted, $capability)) {
            return;
        }

        throw new AccessDeniedHttpException('No tienes permiso para realizar esta operación.');
    }

    /**
     * Read-only shared resources may be visible through more than one product
     * capability. This keeps customer creation from depending on access to the
     * Settings screen while preserving the strict capability checks for writes.
     *
     * @param  list<string>  $capabilities
     */
    protected function authorizeAnyCapability(Request $request, array $capabilities): void
    {
        $session = $request->attributes->get('consolidated.session');
        $granted = is_array($session) ? (array) ($session['capabilities'] ?? []) : [];
        foreach ($capabilities as $capability) {
            if (CapabilityCatalog::allows($granted, $capability)) {
                return;
            }
        }

        throw new AccessDeniedHttpException('No tienes permiso para realizar esta operación.');
    }
}
