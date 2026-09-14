<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Postgres;

use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\InvoiceDraft;
use App\Context\V3\Modules\Fiscal\Invoice\Domain\Repository\InvoiceDraftRepositoryInterface;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models\InvoiceDraftModel;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Mappers\InvoiceDraftMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class InvoiceDraftRepository implements InvoiceDraftRepositoryInterface
{
    public function __construct(
        private readonly InvoiceDraftMapper $mapper,
    ) {}

    public function all(string $userId): array
    {
        $records = InvoiceDraftModel::query()
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();

        return $records->map(fn (InvoiceDraftModel $m): InvoiceDraft => $this->mapper->toDomain($m))->all();
    }

    public function current(string $userId): ?InvoiceDraft
    {
        $record = InvoiceDraftModel::query()
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->first();

        return $record !== null ? $this->mapper->toDomain($record) : null;
    }

    public function save(string $userId, array $payload, ?int $revision): InvoiceDraft
    {
        return DB::connection('master_v3')->transaction(function () use ($userId, $payload, $revision): InvoiceDraft {
            $existing = InvoiceDraftModel::query()
                ->where('user_id', $userId)
                ->orderBy('updated_at', 'desc')
                ->first();

            if ($existing !== null) {
                $expectedRevision = $revision ?? $existing->revision;
                if ((int) $existing->revision !== (int) $expectedRevision) {
                    throw new \DomainException('draft_revision_conflict');
                }

                $existing->update([
                    'payload' => $payload,
                    'revision' => $existing->revision + 1,
                    'expires_at' => now()->addDays(30),
                ]);
                $existing->refresh();

                return $this->mapper->toDomain($existing);
            }

            $draft = InvoiceDraftModel::query()->create([
                'public_id' => Str::uuid()->toString(),
                'user_id' => $userId,
                'revision' => 1,
                'payload' => $payload,
                'expires_at' => now()->addDays(30),
            ]);

            return $this->mapper->toDomain($draft);
        });
    }

    public function delete(string $userId, ?string $publicId, ?int $revision): void
    {
        $query = InvoiceDraftModel::query()->where('user_id', $userId);

        if ($publicId !== null) {
            $query->where('public_id', $publicId);
        }

        $query->delete();
    }
}
