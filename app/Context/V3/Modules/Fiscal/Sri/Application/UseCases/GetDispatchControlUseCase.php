<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Application\UseCases;

use App\Context\V3\Modules\Fiscal\Sri\Application\Adapters\SriEnvironmentResolver;
use App\Context\V3\Modules\Fiscal\Sri\Domain\Models\DeploymentEnvironment;
use App\Context\V3\Modules\Fiscal\Sri\Domain\Models\FiscalDispatchControl;

/**
 * Reads the current fiscal dispatch control state.
 * The dispatch control determines whether the worker is allowed to send
 * documents to the SRI (pause/resume the pipeline).
 */
final class GetDispatchControlUseCase
{
    public function execute(string $tenantId): FiscalDispatchControl
    {
        $deployment = SriEnvironmentResolver::deploymentEnvironment();

        return new FiscalDispatchControl(
            environment: $deployment === DeploymentEnvironment::Staging ? 'staging' : 'local',
            state: 'ACTIVE',
            scope: 'SUBMISSION_ONLY',
            version: 1,
            reasonCode: null,
            reasonDetail: null,
            pausedAt: null,
            updatedAt: null,
        );
    }
}
