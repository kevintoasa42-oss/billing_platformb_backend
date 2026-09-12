<?php

namespace App\Contexto\Enterprise\Aplicacion\DTOs;

class EmpresaDTO
{
    public function __construct(
        public ?int $id = null,
        public ?string $nombre = null,
        public ?string $ruc = null,
        public ?string $tradename = null,
        public ?string $matrixname = null,
        public ?string $telefono = null,
        public ?string $correo_corporativo = null,
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
            nombre: $data['nombre'] ?? null,
            ruc: $data['ruc'] ?? null,
            tradename: $data['tradename'] ?? null,
            matrixname: $data['matrixname'] ?? null,
            telefono: $data['telefono'] ?? null,
            correo_corporativo: $data['correo_corporativo'] ?? null,
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
            'nombre' => $this->nombre,
            'ruc' => $this->ruc,
            'tradename' => $this->tradename,
            'matrixname' => $this->matrixname,
            'telefono' => $this->telefono,
            'correo_corporativo' => $this->correo_corporativo,
        ];
    }
}
