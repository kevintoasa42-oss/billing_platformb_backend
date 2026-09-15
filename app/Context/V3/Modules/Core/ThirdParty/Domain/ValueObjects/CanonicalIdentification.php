<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Canonical representation of an Ecuadorian fiscal identity.
 *
 * The database uniqueness rule is tenant + identification type + this value.
 * Keeping the normalization here prevents each adapter from inventing a
 * slightly different comparison rule.
 */
final class CanonicalIdentification
{
    public const TYPES = ['04', '05', '06', '07'];

    private function __construct(
        public readonly string $type,
        public readonly string $value,
    ) {}

    public static function from(?string $type, ?string $value): ?self
    {
        $normalized = self::normalize($value);
        if ($normalized === '') {
            return null;
        }

        return new self(self::normalizeType($type, $normalized), $normalized);
    }

    public static function normalize(?string $value): string
    {
        return strtoupper((string) preg_replace('/\s+/', '', trim((string) $value)));
    }

    public static function normalizeType(?string $type, ?string $identification = null): string
    {
        $value = strtoupper(trim((string) $type));
        $value = match ($value) {
            'RUC' => '04',
            'CED', 'CI', 'CÉDULA' => '05',
            'PAS', 'PASSPORT' => '06',
            'CF', 'CONSUMIDOR FINAL' => '07',
            default => $value,
        };

        if ($value === '') {
            $value = strlen(self::normalize($identification)) === 13 ? '04' : '05';
        }

        if (! in_array($value, self::TYPES, true)) {
            throw new InvalidArgumentException('El tipo de identificación fiscal no es válido.');
        }

        return $value;
    }

    /** @return array{identification_type:string,identification:string} */
    public function toArray(): array
    {
        return [
            'identification_type' => $this->type,
            'identification' => $this->value,
        ];
    }
}
