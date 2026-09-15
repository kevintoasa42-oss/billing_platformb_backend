<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ThirdPartyAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $identification = $this->query('identification', $this->query('identification_number'));
        $type = $this->query('identification_type');

        if (is_string($type)) {
            $type = strtoupper(str_replace('É', 'E', trim($type)));
            $type = match ($type) {
                'RUC' => '04',
                'CED', 'CI', 'CEDULA' => '05',
                'PAS', 'PASSPORT' => '06',
                'CF', 'CONSUMIDOR FINAL' => '07',
                '' => null,
                default => $type,
            };
        }

        $this->merge([
            'identification' => $identification,
            'identification_type' => $type,
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'identification' => ['nullable', 'string', 'max:50'],
            'identification_type' => ['nullable', 'string', 'in:04,05,06,07'],
            'exclude_id' => ['nullable', 'string', 'max:100'],
        ];
    }
}
