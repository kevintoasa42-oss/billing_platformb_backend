<?php

declare(strict_types=1);

namespace App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use LogicException;

/**
 * Base model for V3 core tables that carry a tenant_id column.
 *
 * Tenant isolation is enforced by PostgreSQL RLS (set via the
 * tenant.v3.context middleware). This global scope adds a second
 * application-level guard so queries are always tenant-bound even
 * when RLS is not active (e.g. console, tests).
 */
abstract class TenantScopedModel extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $query): void {
            $tenantId = static::resolveTenantId($query->getModel());
            $query->where($query->qualifyColumn('tenant_id'), $tenantId);
        });

        static::creating(function (Model $model): void {
            $tenantId = static::resolveTenantId($model);
            $existing = $model->getAttribute('tenant_id');
            if ($existing !== null && (string) $existing !== $tenantId) {
                throw new LogicException('El tenant del registro no coincide con el contexto activo.');
            }
            $model->setAttribute('tenant_id', $tenantId);
        });

        static::updating(function (Model $model): void {
            if (! $model->isDirty('tenant_id')) {
                return;
            }

            $tenantId = static::resolveTenantId($model);
            if ((string) $model->getAttribute('tenant_id') !== $tenantId) {
                throw new LogicException('No se puede cambiar el tenant de un registro.');
            }
        });
    }

    private static function resolveTenantId(Model $model): string
    {
        // 1. Try the request attribute set by AuthenticateV3SessionCookie.
        if (function_exists('request') && app()->bound('request')) {
            $request = request();
            $tenantId = $request->attributes->get('v3.tenant_id');
            if (is_string($tenantId) && Str::isUuid($tenantId)) {
                return $tenantId;
            }
        }

        // 2. Fall back to the PostgreSQL session setting (app.tenant_id)
        //    set by CaptureTenantContext / LaravelMasterV3TenantContextConfigurator.
        $connection = $model->getConnection();
        $row = $connection->selectOne("SELECT current_setting('app.tenant_id', true) AS tenant_id");
        $tenantId = trim((string) ($row?->tenant_id ?? ''));

        if ($tenantId === '' || ! Str::isUuid($tenantId)) {
            throw new LogicException('El contexto tenant es obligatorio para acceder al núcleo V3.');
        }

        return $tenantId;
    }
}
