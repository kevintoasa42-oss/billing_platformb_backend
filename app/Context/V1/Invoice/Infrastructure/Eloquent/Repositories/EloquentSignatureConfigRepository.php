<?php

namespace App\Context\V1\Invoice\Infrastructure\Eloquent\Repositories;

use App\Context\V1\Invoice\Domain\Repositories\SignatureConfigRepositoryInterface;
use App\Models\EnterpriseSignatureModel;
use App\Models\SignatureModel;

class EloquentSignatureConfigRepository implements SignatureConfigRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getActiveConfig(?int $carrierId, int $enterpriseId): ?object
    {
        if ($carrierId !== null) {
            // Carrier signature (tenant DB)
            $signature = SignatureModel::where('carrier_id', $carrierId)
                ->where('status', true)
                ->first();
        } else {
            // Enterprise signature (central DB)
            $signature = EnterpriseSignatureModel::where('enterprise_id', $enterpriseId)
                ->where('status', true)
                ->first();
        }

        if (!$signature) {
            return null;
        }

        return (object) [
            'environment' => $signature->environment, // 'pruebas' or 'produccion'
            'emission_type' => (bool) $signature->emission_type, // true=normal, false=contingencia
        ];
    }
}
