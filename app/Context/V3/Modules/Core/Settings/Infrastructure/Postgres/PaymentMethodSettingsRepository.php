<?php

namespace App\Context\V3\Modules\Core\Settings\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Settings\Domain\Models\PaymentMethod;
use App\Context\V3\Modules\Core\Settings\Domain\Repository\PaymentMethodSettingsRepositoryInterface;
use App\Context\V3\Modules\Core\Settings\Infrastructure\Laravel\Eloquent\Models\TenantSettingsModel;

class PaymentMethodSettingsRepository implements PaymentMethodSettingsRepositoryInterface
{
    public function all(): array
    {
        $row = TenantSettingsModel::query()->first();

        $stored = $row?->payment_method_settings ?? [];

        if ($stored === []) {
            $stored = PaymentMethod::defaults();
            $this->persist($stored, $row);
        }

        return array_map(fn (array $data): PaymentMethod => PaymentMethod::fromArray($data), $stored);
    }

    public function update(string $code, ?string $alias, ?bool $isActive): PaymentMethod
    {
        $row = TenantSettingsModel::query()->first();

        $methods = $row?->payment_method_settings ?? [];

        if ($methods === []) {
            $methods = PaymentMethod::defaults();
        }

        $found = false;
        $result = null;
        foreach ($methods as &$method) {
            if ((string) ($method['code'] ?? '') !== $code) {
                continue;
            }
            if ($alias !== null) {
                $method['alias'] = $alias;
            }
            $method['display_name'] = $method['alias'] ?: $method['name'];
            if ($isActive !== null) {
                $method['is_active'] = $isActive;
            }
            $found = true;
            $result = $method;
            break;
        }
        unset($method);

        if (! $found) {
            throw new \DomainException('El método de pago no existe.');
        }

        $this->persist(array_values($methods), $row);

        return PaymentMethod::fromArray($result);
    }

    /**
     * @param  list<array<string, mixed>>  $methods
     */
    private function persist(array $methods, ?TenantSettingsModel $row): void
    {
        if ($row === null) {
            TenantSettingsModel::query()->create([
                'payment_method_settings' => $methods,
            ]);
        } else {
            $row->update(['payment_method_settings' => $methods]);
        }
    }
}
