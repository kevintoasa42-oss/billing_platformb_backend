<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Application\UseCases;

use App\Context\V3\Modules\Fiscal\Sri\Domain\Models\FiscalDispatchControl;
use RuntimeException;

/**
 * Updates the fiscal dispatch control (pause/resume the SRI pipeline).
 * The global fiscal switch cannot be modified from the consolidated lab.
 */
final class UpdateDispatchControlUseCase
{
    /**
     * @param array{state?: string, reason_code?: string, reason_detail?: string} $input
     */
    public function execute(string $tenantId, array $input): FiscalDispatchControl
    {
        throw new RuntimeException(
            'El interruptor fiscal global no se modifica desde el laboratorio consolidado.',
        );
    }
}
