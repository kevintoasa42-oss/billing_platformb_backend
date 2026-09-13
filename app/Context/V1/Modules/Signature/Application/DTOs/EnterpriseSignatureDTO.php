<?php

namespace App\Context\V1\Modules\Signature\Application\DTOs;

class EnterpriseSignatureDTO
{
    public function __construct(
        public ?int $id = null,
        public ?int $enterprise_id = null,
        public ?string $file_name = null,
        public ?string $file_path = null,
        public ?string $password = null,
        public ?string $expires_at = null,
        public ?string $environment = null,
        public bool $emission_type = true,
        public bool $status = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            enterprise_id: $data['enterprise_id'] ?? null,
            file_name: $data['file_name'] ?? null,
            file_path: $data['file_path'] ?? null,
            password: $data['password'] ?? null,
            expires_at: $data['expires_at'] ?? null,
            environment: $data['environment'] ?? null,
            emission_type: $data['emission_type'] ?? true,
            status: $data['status'] ?? true,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'enterprise_id' => $this->enterprise_id,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'password' => $this->password,
            'expires_at' => $this->expires_at,
            'environment' => $this->environment,
            'emission_type' => $this->emission_type,
            'status' => $this->status,
        ];
    }
}
