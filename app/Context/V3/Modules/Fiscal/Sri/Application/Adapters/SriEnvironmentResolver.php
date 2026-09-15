<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Application\Adapters;

use App\Context\V3\Modules\Fiscal\Sri\Domain\Models\DeploymentEnvironment;
use App\Context\V3\Modules\Fiscal\Sri\Domain\Models\SriEnvironment;
use RuntimeException;

/**
 * Resolves the effective SRI environment from configuration and deployment.
 * The deployment ceiling (APP_ENV) can never be escalated.
 */
final class SriEnvironmentResolver
{
    public static function resolve(
        ?string $tenantMode = null,
        ?string $configuredMode = null,
        ?string $deploymentEnv = null,
    ): SriEnvironment {
        $deployment = DeploymentEnvironment::fromRuntime($deploymentEnv ?? config('app.env', 'local'));

        $mode = self::controlMode($configuredMode) ?? self::tenantMode($tenantMode) ?? self::defaultFor($deployment);

        self::assertAllowed($deployment, $mode);

        return $mode;
    }

    public static function deploymentEnvironment(): DeploymentEnvironment
    {
        return DeploymentEnvironment::fromRuntime(config('app.env', 'local'));
    }

    private static function controlMode(?string $configuredMode): ?SriEnvironment
    {
        if ($configuredMode === null) {
            $configuredMode = (string) config('services.sri.mode', '');
        }

        if ($configuredMode === '') {
            return null;
        }

        $candidate = SriEnvironment::fromConfigured($configuredMode);
        if ($candidate === null) {
            throw new RuntimeException('El modo SRI configurado no es valido. Valores permitidos: mock, celcer, sri.');
        }

        return $candidate;
    }

    private static function tenantMode(?string $mode): ?SriEnvironment
    {
        if ($mode === null) {
            return null;
        }

        return SriEnvironment::fromTenantMode($mode);
    }

    private static function defaultFor(DeploymentEnvironment $environment): SriEnvironment
    {
        return match ($environment) {
            DeploymentEnvironment::Production => SriEnvironment::Sri,
            DeploymentEnvironment::Staging, DeploymentEnvironment::Local => SriEnvironment::Celcer,
        };
    }

    private static function assertAllowed(DeploymentEnvironment $deployment, SriEnvironment $mode): void
    {
        if ($deployment === DeploymentEnvironment::Local && $mode === SriEnvironment::Sri) {
            throw new RuntimeException('El ambiente local no puede alcanzar el SRI de produccion.');
        }

        if ($deployment === DeploymentEnvironment::Local && $mode === SriEnvironment::Celcer) {
            return;
        }

        if ($deployment === DeploymentEnvironment::Staging && $mode === SriEnvironment::Sri) {
            throw new RuntimeException('El ambiente staging no puede alcanzar el SRI de produccion.');
        }
    }
}
