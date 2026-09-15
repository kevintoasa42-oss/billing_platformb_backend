<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\DTOs;

final class ThirdPartyUpdateDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $identification = null,
        public readonly ?bool $mustInvoice = null,
        public readonly ?string $identificationType = null,
        public readonly ?string $address = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?int $customerTypeId = null,
        public readonly ?bool $isActive = null,
        public readonly ?string $role = null,
        /** @var array<int, string>|null */
        public readonly ?array $roles = null,
        /** @var array<int, array<string, mixed>>|null */
        public readonly ?array $activities = null,
        public readonly ?string $plate = null,
        /** @var array<string, mixed>|null */
        public readonly ?array $customFields = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            identification: $data['identification'] ?? null,
            mustInvoice: $data['must_invoice'] ?? null,
            identificationType: $data['identification_type'] ?? null,
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            customerTypeId: $data['customer_type_id'] ?? null,
            isActive: $data['is_active'] ?? null,
            role: $data['role'] ?? null,
            roles: array_key_exists('roles', $data) && is_array($data['roles'])
                ? array_values(array_unique(array_filter($data['roles'], static fn ($role): bool => is_string($role) && trim($role) !== '')))
                : null,
            activities: $data['activities'] ?? null,
            plate: $data['plate'] ?? null,
            customFields: array_key_exists('custom_fields', $data) && is_array($data['custom_fields']) ? $data['custom_fields'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = array_filter([
            'name' => $this->name,
            'identification' => $this->identification,
            'must_invoice' => $this->mustInvoice,
            'identification_type' => $this->identificationType,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'customer_type_id' => $this->customerTypeId,
            'is_active' => $this->isActive,
        ], fn ($value) => $value !== null);

        if ($this->activities !== null) {
            $data['activities'] = $this->activities;
        }

        if ($this->roles !== null) {
            $data['roles'] = $this->roles;
        } elseif ($this->role !== null) {
            $data['role'] = $this->role;
        }

        if ($this->customFields !== null) {
            $data['custom_fields'] = $this->customFields;
        }

        return $data;
    }
}
