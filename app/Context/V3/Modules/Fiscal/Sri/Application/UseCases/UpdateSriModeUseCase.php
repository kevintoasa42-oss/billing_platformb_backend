<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Application\UseCases;

use App\Context\V3\Modules\Fiscal\Sri\Application\Adapters\SriEnvironmentResolver;
use App\Context\V3\Modules\Fiscal\Sri\Domain\Models\DeploymentEnvironment;
use App\Context\V3\Modules\Fiscal\Sri\Domain\Models\SriEnvironment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Updates the SRI authorization mode for the current tenant.
 * Only platform admins can change the mode.
 */
final class UpdateSriModeUseCase
{
    public function execute(string $tenantId, string $mode): SriEnvironment
    {
        $resolved = SriEnvironment::fromTenantMode($mode);
        if ($resolved === null) {
            throw new RuntimeException('El modo SRI no es valido. Valores permitidos: mock, celcer, sri.');
        }

        // Check deployment ceiling
        $deployment = SriEnvironmentResolver::deploymentEnvironment();
        if ($deployment === DeploymentEnvironment::Local && $resolved === SriEnvironment::Celcer) {
            throw new RuntimeException('CELCER real no esta habilitado en el laboratorio.');
        }
        if ($deployment === DeploymentEnvironment::Local && $resolved === SriEnvironment::Sri) {
            throw new RuntimeException('El ambiente local no puede alcanzar el SRI de produccion.');
        }
        if ($deployment === DeploymentEnvironment::Staging && $resolved === SriEnvironment::Sri) {
            throw new RuntimeException('El ambiente staging no puede alcanzar el SRI de produccion.');
        }

        // Update the tenant's authorization_mode
        DB::connection('master_v3')->statement(
            "UPDATE core.enterprise_tax_settings
             SET authorization_mode = ?, updated_at = now()
             WHERE tenant_id = ?",
            [$resolved->value, $tenantId],
        );

        return $resolved;
    }
}
