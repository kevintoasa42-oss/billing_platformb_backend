<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Models;

/**
 * Pure domain model for a ThirdParty (customer, carrier, supplier, member).
 */
final class ThirdParty
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $identification = null,
        public readonly ?bool $mustInvoice = null,
        public readonly ?string $legacyId = null,
        public readonly ?string $identificationType = null,
        public readonly ?string $address = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?int $customerTypeId = null,
        public readonly ?bool $isActive = null,
        /** @var array<int, string>|null */
        public readonly ?array $roles = null,
        /** @var array<int, array<string, mixed>>|null */
        public readonly ?array $activities = null,
        /** @var array<string, mixed>|null */
        public readonly ?array $customFields = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            name: $data['name'],
            identification: $data['identification'] ?? null,
            mustInvoice: $data['must_invoice'] ?? null,
            legacyId: $data['legacy_id'] ?? null,
            identificationType: $data['identification_type'] ?? null,
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            customerTypeId: $data['customer_type_id'] ?? null,
            isActive: $data['is_active'] ?? null,
            roles: $data['roles'] ?? null,
            activities: $data['activities'] ?? null,
            customFields: $data['custom_fields'] ?? null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'name' => $this->name,
            'identification' => $this->identification,
            'must_invoice' => $this->mustInvoice,
            'legacy_id' => $this->legacyId,
            'identification_type' => $this->identificationType,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'customer_type_id' => $this->customerTypeId,
            'is_active' => $this->isActive,
            'roles' => $this->roles,
            'activities' => $this->activities,
            'custom_fields' => $this->customFields,
        ];
    }
}
