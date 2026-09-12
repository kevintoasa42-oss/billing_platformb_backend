<?php

namespace App\Contexto\Menu\Aplicacion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'ruta' => 'nullable|string|max:255',
            'icono' => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer|exists:menus,id',
            'orden' => 'nullable|integer',
        ];
    }

    /**
     * Convierte los datos validados a un MenuDTO.
     *
     * @param  array  $data
     * @return \App\Contexto\Menu\Aplicacion\DTOs\MenuDTO
     */
    public static function toDTO(array $data): \App\Contexto\Menu\Aplicacion\DTOs\MenuDTO
    {
        return \App\Contexto\Menu\Aplicacion\DTOs\MenuDTO::fromArray($data);
    }
}
