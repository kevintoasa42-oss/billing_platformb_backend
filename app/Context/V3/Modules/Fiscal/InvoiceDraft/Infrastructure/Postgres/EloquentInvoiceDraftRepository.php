<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\InvoiceDraft\Infrastructure\Postgres;

use App\Context\V3\Modules\Fiscal\InvoiceDraft\Domain\Exceptions\InvoiceDraftException;
use App\Context\V3\Modules\Fiscal\InvoiceDraft\Domain\Models\InvoiceDraft;
use App\Context\V3\Modules\Fiscal\InvoiceDraft\Domain\Repository\InvoiceDraftRepositoryInterface;
use App\Context\V3\Modules\Fiscal\InvoiceDraft\Infrastructure\Laravel\Eloquent\Models\InvoiceDraftModel;
use App\Context\V3\Modules\Fiscal\InvoiceDraft\Infrastructure\Mappers\InvoiceDraftMapper;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class EloquentInvoiceDraftRepository implements InvoiceDraftRepositoryInterface
{
    private const TTL_DAYS = 30;

    public function __construct(
        private InvoiceDraftMapper $mapper,
    ) {}

    public function save(string $userId, array $payload, ?int $expectedRevision): InvoiceDraft
    {
        try {
            return DB::connection('master_v3')->transaction(function () use ($userId, $payload, $expectedRevision): InvoiceDraft {
                $draft = InvoiceDraftModel::query()
                    ->where('user_id', $userId)
                    ->lockForUpdate()
                    ->first();

                if ($draft !== null && $expectedRevision !== null && $draft->revision !== $expectedRevision) {
                    throw new InvoiceDraftException(
                        'El borrador cambió en otra pestaña. Recarga antes de guardar.',
                        'invoice_draft_revision_conflict',
                        409,
                    );
                }

                $expiresAt = now()->addDays(self::TTL_DAYS);
                if ($draft === null) {
                    $draft = InvoiceDraftModel::query()->create([
                        'public_id' => (string) Str::uuid(),
                        'user_id' => $userId,
                        'revision' => 1,
                        'payload' => $payload,
                        'expires_at' => $expiresAt,
                    ]);
                } else {
                    $draft->forceFill([
                        'payload' => $payload,
                        'revision' => $draft->revision + 1,
                        'expires_at' => $expiresAt,
                    ])->save();
                }

                return $this->mapper->toDomain($draft->refresh());
            });
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                throw new InvoiceDraftException(
                    'El borrador cambió en otra pestaña. Recarga antes de guardar.',
                    'invoice_draft_revision_conflict',
                    409,
                );
            }

            throw $exception;
        }
    }
}
