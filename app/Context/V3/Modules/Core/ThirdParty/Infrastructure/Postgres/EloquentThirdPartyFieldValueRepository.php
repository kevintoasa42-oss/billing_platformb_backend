<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Exceptions\ThirdPartyException;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldValueRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyFieldDefinitionModel;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyFieldValueModel;

final class EloquentThirdPartyFieldValueRepository implements ThirdPartyFieldValueRepositoryInterface
{
    /**
     * @param  list<string>  $roles
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function replace(string $thirdPartyId, array $roles, array $values): array
    {
        $definitions = ThirdPartyFieldDefinitionModel::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->keyBy('code');

        $normalized = $this->normalizeValues($definitions->all(), $roles, $values);

        foreach ($normalized as $code => $value) {
            /** @var ThirdPartyFieldDefinitionModel $definition */
            $definition = $definitions->get($code);

            ThirdPartyFieldValueModel::query()->updateOrCreate(
                [
                    'third_party_id' => $thirdPartyId,
                    'definition_id' => (string) $definition->getKey(),
                ],
                [
                    'value' => $value,
                    'updated_at' => now(),
                ],
            );
        }

        return $normalized;
    }

    /**
     * @param  array<string, ThirdPartyFieldDefinitionModel>  $definitions
     * @param  list<string>  $roles
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function normalizeValues(array $definitions, array $roles, array $values): array
    {
        $normalized = [];

        foreach ($values as $code => $value) {
            if (! is_string($code) || ! isset($definitions[$code])) {
                throw new ThirdPartyException(
                    'El campo de tercero no existe o está inactivo: '.(string) $code,
                    'third_party_fields_invalid',
                );
            }

            $definition = $definitions[$code];
            if (! $this->definitionApplies((string) $definition->getAttribute('scope'), $roles)) {
                throw new ThirdPartyException(
                    'El campo de tercero no aplica al rol actual: '.$code,
                    'third_party_fields_invalid',
                );
            }

            $value = $this->validateValue($definition, $value, $code);
            if ($value !== null) {
                $normalized[$code] = $value;
            }
        }

        foreach ($definitions as $definition) {
            if (! $this->definitionApplies((string) $definition->getAttribute('scope'), $roles)
                || ! (bool) $definition->getAttribute('is_required')) {
                continue;
            }

            $code = (string) $definition->getAttribute('code');
            if (! array_key_exists($code, $normalized) || $this->isBlank($normalized[$code])) {
                throw new ThirdPartyException(
                    'El campo de tercero obligatorio no fue informado: '.(string) $definition->getAttribute('label'),
                    'third_party_fields_invalid',
                );
            }
        }

        return $normalized;
    }

    /** @param list<string> $roles */
    private function definitionApplies(string $scope, array $roles): bool
    {
        return $scope === 'both' || in_array($scope, $roles, true);
    }

    private function validateValue(ThirdPartyFieldDefinitionModel $definition, mixed $value, string $code): mixed
    {
        if ($this->isBlank($value)) {
            if ((bool) $definition->getAttribute('is_required')) {
                throw new ThirdPartyException('El campo de tercero obligatorio no fue informado: '.$code, 'third_party_fields_invalid');
            }

            return null;
        }

        $validation = $definition->getAttribute('validation');
        $validation = is_array($validation) ? $validation : [];

        return match ((string) $definition->getAttribute('data_type')) {
            'text' => $this->validateText($value, $validation, $code),
            'number' => $this->validateNumber($value, $validation, $code),
            'date' => $this->validateDate($value, $code),
            'boolean' => $this->validateBoolean($value, $code),
            'select' => $this->validateSelect($value, $validation, $code),
            default => throw new ThirdPartyException('El tipo de dato del campo no es válido: '.$code, 'third_party_fields_invalid'),
        };
    }

    /** @param array<string, mixed> $validation */
    private function validateText(mixed $value, array $validation, string $code): string
    {
        if (! is_scalar($value)) {
            throw new ThirdPartyException('El valor del campo debe ser texto: '.$code, 'third_party_fields_invalid');
        }

        $value = trim((string) $value);
        $min = isset($validation['min_length']) ? max(0, (int) $validation['min_length']) : null;
        $max = isset($validation['max_length']) ? max(0, (int) $validation['max_length']) : null;
        if (($min !== null && mb_strlen($value) < $min) || ($max !== null && mb_strlen($value) > $max)) {
            throw new ThirdPartyException('El valor del campo no cumple su longitud: '.$code, 'third_party_fields_invalid');
        }

        return $value;
    }

    /** @param array<string, mixed> $validation */
    private function validateNumber(mixed $value, array $validation, string $code): float|int
    {
        if (! is_numeric($value)) {
            throw new ThirdPartyException('El valor del campo debe ser numérico: '.$code, 'third_party_fields_invalid');
        }

        $number = (float) $value;
        if (isset($validation['min']) && $number < (float) $validation['min']) {
            throw new ThirdPartyException('El valor del campo es menor al mínimo permitido: '.$code, 'third_party_fields_invalid');
        }
        if (isset($validation['max']) && $number > (float) $validation['max']) {
            throw new ThirdPartyException('El valor del campo supera el máximo permitido: '.$code, 'third_party_fields_invalid');
        }

        return fmod($number, 1.0) === 0.0 ? (int) $number : $number;
    }

    private function validateDate(mixed $value, string $code): string
    {
        $value = (string) $value;
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new ThirdPartyException('El valor del campo debe ser una fecha YYYY-MM-DD: '.$code, 'third_party_fields_invalid');
        }

        return $value;
    }

    private function validateBoolean(mixed $value, string $code): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value) && in_array($value, [0, 1], true)) {
            return $value === 1;
        }
        if (is_string($value) && in_array(strtolower(trim($value)), ['true', 'false', '0', '1'], true)) {
            return in_array(strtolower(trim($value)), ['true', '1'], true);
        }

        throw new ThirdPartyException('El valor del campo debe ser booleano: '.$code, 'third_party_fields_invalid');
    }

    /** @param array<string, mixed> $validation */
    private function validateSelect(mixed $value, array $validation, string $code): string
    {
        $value = trim((string) $value);
        $options = array_map(
            static fn (mixed $option): string => (string) $option,
            is_array($validation['options'] ?? null) ? $validation['options'] : [],
        );
        if (! in_array($value, $options, true)) {
            throw new ThirdPartyException('El valor del campo no es una opción válida: '.$code, 'third_party_fields_invalid');
        }

        return $value;
    }

    private function isBlank(mixed $value): bool
    {
        return $value === null || (is_string($value) && trim($value) === '');
    }
}
