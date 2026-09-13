<?php

namespace App\Context\V1\Modules\Menu\Application\Http\Requests;

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
            'name' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer|exists:menus,id',
            'order' => 'nullable|integer',
        ];
    }

    /**
     * Convierte los datos validados a un MenuDTO.
     *
     * @param  array  $data
     * @return \App\Context\V1\Modules\Menu\Application\DTOs\MenuDTO
     */
    public static function toDTO(array $data): \App\Context\V1\Modules\Menu\Application\DTOs\MenuDTO
    {
        return \App\Context\V1\Modules\Menu\Application\DTOs\MenuDTO::fromArray($data);
    }
}
