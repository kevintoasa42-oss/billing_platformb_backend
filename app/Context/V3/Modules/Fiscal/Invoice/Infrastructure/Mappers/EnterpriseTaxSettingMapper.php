<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Mappers;

use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models\EnterpriseTaxSettingModel;

/**
 * Maps an EnterpriseTaxSettingModel (Eloquent) into a plain array.
 * Performs no database queries.
 */
final class EnterpriseTaxSettingMapper
{
    /** @return array<string, mixed> */
    public function toDomain(EnterpriseTaxSettingModel $record): array
    {
        return [
            'company_id' => (string) $record->company_id,
            'has_accounting' => (bool) $record->has_accounting,
            'has_inventory' => (bool) $record->has_inventory,
            'authorization_mode' => (string) $record->authorization_mode,
        ];
    }
}
