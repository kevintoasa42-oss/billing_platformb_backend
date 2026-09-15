<?php

namespace App\Context\V3\Modules\Core\Product\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ProductIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:500'],
            'is_active' => ['nullable', 'string', 'in:0,1,all'],
        ];
    }

    public function searchTerm(): ?string
    {
        return $this->validated('search') ?: $this->validated('q');
    }

    public function perPage(): int
    {
        return (int) $this->validated('per_page', 500);
    }

    public function activeFilter(): ?bool
    {
        return match ($this->validated('is_active', 'all')) {
            '1' => true,
            '0' => false,
            default => null,
        };
    }
}
