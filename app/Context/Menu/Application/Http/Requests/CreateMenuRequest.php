<?php

namespace App\Context\Menu\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMenuRequest extends FormRequest
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
     * @return \App\Context\Menu\Application\DTOs\MenuDTO
     */
    public static function toDTO(array $data): \App\Context\Menu\Application\DTOs\MenuDTO
    {
        return \App\Context\Menu\Application\DTOs\MenuDTO::fromArray($data);
    }
}
