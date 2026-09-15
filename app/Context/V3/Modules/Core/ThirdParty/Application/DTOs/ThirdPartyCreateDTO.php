<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\DTOs;

final class ThirdPartyCreateDTO
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
        public readonly ?string $role = null,
        /** @var array<int, string> */
        public readonly array $roles = [],
        /** @var array<int, array<string, mixed>> */
        public readonly array $activities = [],
        public readonly ?string $plate = null,
        /** @var array<string, mixed> */
        public readonly array $customFields = [],
    ) {}

    /** @param array<string, mixed> $data */
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
            role: $data['role'] ?? null,
            roles: array_values(array_unique(array_filter(
                is_array($data['roles'] ?? null) ? $data['roles'] : (($data['role'] ?? null) !== null ? [$data['role']] : []),
                static fn ($role): bool => is_string($role) && trim($role) !== '',
            ))),
            activities: $data['activities'] ?? [],
            plate: $data['plate'] ?? null,
            customFields: is_array($data['custom_fields'] ?? null) ? $data['custom_fields'] : [],
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
            'role' => $this->role,
            'roles' => $this->roles,
            'activities' => $this->activities,
            'custom_fields' => $this->customFields,
        ];
    }
}
