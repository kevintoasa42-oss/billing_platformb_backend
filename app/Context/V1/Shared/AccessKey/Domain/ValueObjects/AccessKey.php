<?php

namespace App\Context\V1\Shared\AccessKey\Domain\ValueObjects;

/**
 * SRI access key value object (49 digits).
 * Immutable.
 */
final readonly class AccessKey
{
    public function __construct(
        public string $value
    ) {
        if (strlen($value) !== 49 || !ctype_digit($value)) {
            throw new \InvalidArgumentException(
                'Access key must be exactly 49 numeric digits.'
            );
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
