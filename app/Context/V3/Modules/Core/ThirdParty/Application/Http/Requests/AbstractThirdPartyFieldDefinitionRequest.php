<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class AbstractThirdPartyFieldDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $attributes = [];

        if ($this->has('code') && is_string($this->input('code'))) {
            $attributes['code'] = strtolower(trim($this->input('code')));
        }

        if ($this->has('label') && is_string($this->input('label'))) {
            $attributes['label'] = trim($this->input('label'));
        }

        foreach (['scope', 'data_type'] as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $attributes[$field] = strtolower(trim($this->input($field)));
            }
        }

        if ($this->has('validation') && is_array($this->input('validation'))) {
            $validation = $this->input('validation');
            if (is_array($validation['options'] ?? null)) {
                $validation['options'] = array_values(array_filter(
                    array_map(static fn (mixed $option): string => trim((string) $option), $validation['options']),
                    static fn (string $option): bool => $option !== '',
                ));
            }
            $attributes['validation'] = $validation;
        }

        $this->merge($attributes);
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->input('data_type') !== 'select') {
                return;
            }

            $options = data_get($this->input('validation'), 'options');
            if (! is_array($options) || $options === []) {
                $validator->errors()->add('validation.options', 'Un campo de selección debe definir opciones no vacías.');
            }
        }];
    }

    /** @return array<string, array<int, string>> */
    protected function fieldRules(bool $required): array
    {
        $presence = $required ? ['required'] : ['sometimes', 'required'];

        return [
            'code' => [...$presence, 'string', 'max:81', 'regex:/^[a-z][a-z0-9_]{1,80}$/'],
            'label' => [...$presence, 'string', 'max:160'],
            'scope' => ['sometimes', 'in:customer,carrier,both'],
            'data_type' => ['sometimes', 'in:text,number,date,boolean,select'],
            'validation' => ['sometimes', 'array'],
            'validation.options' => ['sometimes', 'array', 'min:1', 'max:100'],
            'validation.options.*' => ['string', 'max:160'],
            'validation.min_length' => ['sometimes', 'integer', 'min:0', 'max:10000'],
            'validation.max_length' => ['sometimes', 'integer', 'min:0', 'max:10000'],
            'validation.min' => ['sometimes', 'numeric'],
            'validation.max' => ['sometimes', 'numeric'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'is_required' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
