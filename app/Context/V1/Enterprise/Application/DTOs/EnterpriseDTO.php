<?php

namespace App\Context\V1\Enterprise\Application\DTOs;

class EnterpriseDTO
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $ruc = null,
        public ?string $tradename = null,
        public ?string $matrix_name = null,
        public ?string $phone = null,
        public ?string $corporate_email = null,
    ) {}

    /**
     * Crea un DTO desde un array de datos de entrada (request).
     *
     * @param  array  $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            ruc: $data['ruc'] ?? null,
            tradename: $data['tradename'] ?? null,
            matrix_name: $data['matrix_name'] ?? null,
            phone: $data['phone'] ?? null,
            corporate_email: $data['corporate_email'] ?? null,
        );
    }

    /**
     * Convierte el DTO a array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ruc' => $this->ruc,
            'tradename' => $this->tradename,
            'matrix_name' => $this->matrix_name,
            'phone' => $this->phone,
            'corporate_email' => $this->corporate_email,
        ];
    }
}
