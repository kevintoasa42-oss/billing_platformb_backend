<?php

namespace App\Context\V3\Modules\Core\Establishment\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\Establishment\Domain\Models\EmissionPoint;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models\EmissionPointModel;
use App\Context\V3\Modules\Fiscal\Infrastructure\Laravel\Eloquent\Models\SequenceModel;

class EmissionPointMapper
{
    public function toDomain(EmissionPointModel $record): EmissionPoint
    {
        return EmissionPoint::fromArray([
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'establishment_id' => $record->establishment_id,
            'sri_code' => $record->sri_code,
            'name' => $record->name,
            'legacy_id' => $record->legacy_id,
            'is_active' => $record->is_active,
            'is_default' => $record->is_default,
            'has_tax_validity' => $record->has_tax_validity,
        ]);
    }

    /**
     * Build EmissionPoint enriched with branch-legacy-id + sequential fields.
     *
     * Requires the `establishment` relation to be eager-loaded on the model.
     */
    public function toBranchDomain(EmissionPointModel $record): EmissionPoint
    {
        $branchLegacyId = $record->establishment?->legacy_id;

        $last = (int) (SequenceModel::query()
            ->where('emission_point_id', $record->id)
            ->where('document_type', 'invoice')
            ->value('last_number') ?? 0);

        return new EmissionPoint(
            id: (string) $record->id,
            tenantId: (string) $record->tenant_id,
            establishmentId: (string) $record->establishment_id,
            sriCode: (string) $record->sri_code,
            name: $record->name,
            legacyId: (int) $record->legacy_id,
            branchLegacyId: (int) $branchLegacyId,
            isActive: (bool) $record->is_active,
            isDefault: (bool) $record->is_default,
            hasTaxValidity: (bool) $record->has_tax_validity,
            lastIssuedSequential: $last,
            nextSequential: $last + 1,
        );
    }

    /**
     * @param  iterable<int, EmissionPointModel>  $records
     * @return array<int, EmissionPoint>
     */
    public function toDomainList(iterable $records): array
    {
        $list = [];
        foreach ($records as $record) {
            $list[] = $this->toDomain($record);
        }

        return $list;
    }

    /**
     * @param  iterable<int, EmissionPointModel>  $records
     * @return array<int, EmissionPoint>
     */
    public function toBranchDomainList(iterable $records): array
    {
        $list = [];
        foreach ($records as $record) {
            $list[] = $this->toBranchDomain($record);
        }

        return $list;
    }

    public function toDatabaseArray(EmissionPoint $emissionPoint): array
    {
        $data = $emissionPoint->toArray();

        if (($data['id'] ?? null) === null) {
            unset($data['id']);
        }

        if (($data['tenant_id'] ?? null) === null) {
            unset($data['tenant_id']);
        }

        if (($data['legacy_id'] ?? null) === null) {
            unset($data['legacy_id']);
        }

        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_default'] = $data['is_default'] ?? false;
        $data['has_tax_validity'] = $data['has_tax_validity'] ?? true;

        return $data;
    }

    /**
     * Normalize an issuance-point number into a 3-digit SRI code.
     */
    public function code(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?: '001';

        return str_pad(substr($digits, -3), 3, '0', STR_PAD_LEFT);
    }
}
