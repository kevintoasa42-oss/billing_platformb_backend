<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Domain\Models;

use UnexpectedValueException;

enum DeploymentEnvironment: string
{
    case Staging = 'staging';
    case Production = 'production';
    case Local = 'local';

    public static function fromRuntime(string $value): self
    {
        return match (strtolower(trim($value))) {
            'production', 'prod' => self::Production,
            'staging', 'stage', 'testing', 'test' => self::Staging,
            'local', 'develop', 'development', 'dev' => self::Local,
            default => throw new UnexpectedValueException('APP_ENV no corresponde a un ambiente fiscal permitido.'),
        };
    }
}
