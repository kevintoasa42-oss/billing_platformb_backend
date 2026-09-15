<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Models;

/**
 * Tenant-bound third party aggregate used by the shared customer, carrier,
 * supplier and member directory.
 *
 * @param  list<string>  $roles
 * @param  array<string, mixed>  $customFields
 */
final readonly class ThirdParty
{
    public function __construct(
        public ?string $id,
        public ?string $tenantId,
        public string $name,
        public string $identification,
        public string $identificationType,
        public ?string $personType,
        public bool $mustInvoice,
        public ?int $legacyId,
        public ?string $address,
        public ?string $phone,
        public ?string $email,
        public ?int $customerTypeId,
        public bool $isActive,
        public array $roles,
        public array $customFields = [],
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (string) $data['id'] : null,
            tenantId: isset($data['tenant_id']) ? (string) $data['tenant_id'] : null,
            name: (string) $data['name'],
            identification: (string) $data['identification'],
            identificationType: (string) $data['identification_type'],
            personType: isset($data['person_type']) ? (string) $data['person_type'] : null,
            mustInvoice: (bool) ($data['must_invoice'] ?? true),
            legacyId: isset($data['legacy_id']) ? (int) $data['legacy_id'] : null,
            address: isset($data['address']) ? (string) $data['address'] : null,
            phone: isset($data['phone']) ? (string) $data['phone'] : null,
            email: isset($data['email']) ? (string) $data['email'] : null,
            customerTypeId: isset($data['customer_type_id']) ? (int) $data['customer_type_id'] : null,
            isActive: (bool) ($data['is_active'] ?? true),
            roles: array_values($data['roles'] ?? []),
            customFields: is_array($data['custom_fields'] ?? null) ? $data['custom_fields'] : [],
        );
    }

    /** @param array<string, mixed> $customFields */
    public function withCustomFields(array $customFields): self
    {
        return new self(
            $this->id,
            $this->tenantId,
            $this->name,
            $this->identification,
            $this->identificationType,
            $this->personType,
            $this->mustInvoice,
            $this->legacyId,
            $this->address,
            $this->phone,
            $this->email,
            $this->customerTypeId,
            $this->isActive,
            $this->roles,
            $customFields,
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
            'identification_type' => $this->identificationType,
            'person_type' => $this->personType,
            'must_invoice' => $this->mustInvoice,
            'legacy_id' => $this->legacyId,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'customer_type_id' => $this->customerTypeId,
            'is_active' => $this->isActive,
            'roles' => $this->roles,
            'custom_fields' => $this->customFields,
        ];
    }
}
