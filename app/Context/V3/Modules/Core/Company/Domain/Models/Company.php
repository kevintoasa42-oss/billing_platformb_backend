<?php

namespace App\Context\V3\Modules\Core\Company\Domain\Models;

class Company
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $ruc = null,
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $legalName = null,
        public readonly ?string $tradeName = null,
        public readonly ?string $matrixAddress = null,
        public readonly ?string $operationsStartDate = null,
        public readonly ?int $cityId = null,
        public readonly ?string $phone = null,
        public readonly ?string $corporateEmail = null,
        /** @var array<int, string>|null */
        public readonly ?array $activityIds = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            ruc: $data['ruc'] ?? null,
            id: $data['id'],
            tenantId: $data['tenant_id'],
            legalName: $data['legal_name'] ?? null,
            tradeName: $data['trade_name'] ?? null,
            matrixAddress: $data['matrix_address'] ?? null,
            operationsStartDate: $data['operations_start_date'] ?? null,
            cityId: $data['city_id'] ?? null,
            phone: $data['phone'] ?? null,
            corporateEmail: $data['corporate_email'] ?? null,
            activityIds: $data['activity_ids'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'name' => $this->name,
            'ruc' => $this->ruc,
            'legal_name' => $this->legalName,
            'trade_name' => $this->tradeName,
            'matrix_address' => $this->matrixAddress,
            'operations_start_date' => $this->operationsStartDate,
            'city_id' => $this->cityId,
            'phone' => $this->phone,
            'corporate_email' => $this->corporateEmail,
            'activity_ids' => $this->activityIds,
        ];
    }
}
