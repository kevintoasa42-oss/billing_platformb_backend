<?php

namespace App\Context\V3\Modules\Core\Settings\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Settings\Domain\Models\PaymentMethod;
use App\Context\V3\Modules\Core\Settings\Domain\Repository\PaymentMethodSettingsRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PaymentMethodSettingsRepository implements PaymentMethodSettingsRepositoryInterface
{
    public function all(): array
    {
        $row = DB::connection('master_v3')
            ->table('core.tenant_settings')
            ->first();

        $stored = [];
        if ($row !== null && $row->payment_method_settings !== null) {
            $stored = is_array($row->payment_method_settings)
                ? $row->payment_method_settings
                : (json_decode((string) $row->payment_method_settings, true) ?: []);
        }

        if ($stored === []) {
            $stored = PaymentMethod::defaults();
            $this->persist($stored, $row);
        }

        return array_map(fn (array $data): PaymentMethod => PaymentMethod::fromArray($data), $stored);
    }

    public function update(string $code, ?string $alias, ?bool $isActive): PaymentMethod
    {
        return DB::connection('master_v3')->transaction(function () use ($code, $alias, $isActive): PaymentMethod {
            $row = DB::connection('master_v3')
                ->table('core.tenant_settings')
                ->first();

            $methods = [];
            if ($row !== null && $row->payment_method_settings !== null) {
                $methods = is_array($row->payment_method_settings)
                    ? $row->payment_method_settings
                    : (json_decode((string) $row->payment_method_settings, true) ?: []);
            }

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
        });
    }

    /**
     * @param  list<array<string, mixed>>  $methods
     */
    private function persist(array $methods, ?object $row): void
    {
        $payload = json_encode($methods, JSON_THROW_ON_ERROR);

        if ($row === null) {
            DB::connection('master_v3')->table('core.tenant_settings')->insert([
                'payment_method_settings' => $payload,
                'updated_at' => now(),
            ]);
        } else {
            DB::connection('master_v3')->table('core.tenant_settings')
                ->where('tenant_id', $row->tenant_id)
                ->update([
                    'payment_method_settings' => $payload,
                    'updated_at' => now(),
                ]);
        }
    }
}
