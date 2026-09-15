<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\DTOs;

final readonly class ThirdPartyCreateDTO
{
    /**
     * @param  list<string>  $roles
     * @param  array<string, mixed>  $customFields
     */
    public function __construct(
        public string $name,
        public string $identification,
        public string $identificationType,
        public ?bool $mustInvoice,
        public ?string $personType,
        public ?int $legacyId,
        public ?string $address,
        public ?string $phone,
        public ?string $email,
        public ?int $customerTypeId,
        public ?bool $isActive,
        public ?string $role,
        public array $roles,
        public array $customFields,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            identification: (string) $data['identification'],
            identificationType: (string) $data['identification_type'],
            mustInvoice: array_key_exists('must_invoice', $data) ? (bool) $data['must_invoice'] : null,
            personType: isset($data['person_type']) ? (string) $data['person_type'] : null,
            legacyId: isset($data['legacy_id']) ? (int) $data['legacy_id'] : null,
            address: isset($data['address']) ? (string) $data['address'] : null,
            phone: isset($data['phone']) ? (string) $data['phone'] : null,
            email: isset($data['email']) ? (string) $data['email'] : null,
            customerTypeId: isset($data['customer_type_id']) ? (int) $data['customer_type_id'] : null,
            isActive: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
            role: isset($data['role']) ? (string) $data['role'] : null,
            roles: is_array($data['roles'] ?? null) ? array_values($data['roles']) : [],
            customFields: is_array($data['custom_fields'] ?? null) ? $data['custom_fields'] : [],
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'identification' => $this->identification,
            'identification_type' => $this->identificationType,
            'must_invoice' => $this->mustInvoice,
            'person_type' => $this->personType,
            'legacy_id' => $this->legacyId,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'customer_type_id' => $this->customerTypeId,
            'is_active' => $this->isActive,
            'role' => $this->role,
            'roles' => $this->roles,
            'custom_fields' => $this->customFields,
        ];
    }
}
