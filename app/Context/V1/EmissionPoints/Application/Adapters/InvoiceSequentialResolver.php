<?php

namespace App\Context\V1\EmissionPoints\Application\Adapters;

use App\Context\V1\EmissionPoints\Domain\Exceptions\EmissionPointNotFoundException;
use App\Context\V1\EmissionPoints\Infrastructure\Laravel\Eloquent\Models\EmissionPointModel;
use App\Context\V1\EmissionPoints\Infrastructure\Laravel\Eloquent\Models\EmissionPointSequenceModel;
use App\Models\InvoiceHeaderModel;
use Illuminate\Support\Facades\DB;

/**
 * Resolves SRI codes and generates unique sequentials for invoices.
 *
 * Uses a row-level lock (lockForUpdate) on the emission_point_sequences
 * counter to guarantee that no two concurrent requests obtain the same
 * sequential.  If the frontend sends a specific sequential that is already
 * used, a fresh one is allocated from the counter instead.
 */
final class InvoiceSequentialResolver implements InvoiceSequentialResolverInterface
{
    public function resolve(
        int $branchOfficeId,
        int $emissionPointId,
        ?string $requestedSequential = null,
        ?int $excludeInvoiceId = null,
    ): array {
        return DB::connection('tenant')->transaction(function () use ($branchOfficeId, $emissionPointId, $requestedSequential, $excludeInvoiceId): array {
            // Lock the emission point + branch office row
            $point = EmissionPointModel::query()
                ->with('branchOffice:id,code_sri')
                ->where('branch_office_id', $branchOfficeId)
                ->whereKey($emissionPointId)
                ->lockForUpdate()
                ->first();

            if (!$point || !$point->branchOffice) {
                throw new EmissionPointNotFoundException($branchOfficeId, $emissionPointId, null);
            }

            // Lock the counter row (create if missing)
            $counter = EmissionPointSequenceModel::query()
                ->where('emission_point_id', $point->id)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = EmissionPointSequenceModel::create([
                    'branch_office_id' => $branchOfficeId,
                    'emission_point_id' => $point->id,
                    'next_sequential' => 1,
                ]);
            }

            $establishment = (string) $point->branchOffice->code_sri;
            $emissionPointCode = (string) $point->emission_point;
            $nextFromCounter = (int) $counter->next_sequential;

            $sequential = $this->determineSequential(
                $branchOfficeId,
                $emissionPointId,
                $requestedSequential,
                $nextFromCounter,
                $excludeInvoiceId,
            );

            // Advance the counter past the sequential we are about to use
            $counter->update([
                'next_sequential' => max($nextFromCounter, $sequential + 1),
            ]);

            return [
                'establishment' => $establishment,
                'emission_point' => $emissionPointCode,
                'sequential' => str_pad((string) $sequential, 9, '0', STR_PAD_LEFT),
            ];
        });
    }

    /**
     * Decide which sequential to use.
     *
     * 1. If the frontend sent one and it is not already used, keep it.
     * 2. Otherwise, take the next from the atomic counter.
     */
    private function determineSequential(
        int $branchOfficeId,
        int $emissionPointId,
        ?string $requestedSequential,
        int $nextFromCounter,
        ?int $excludeInvoiceId,
    ): int {
        if ($requestedSequential !== null && $requestedSequential !== '') {
            $requested = (int) ltrim($requestedSequential, '0');
            if ($requested < 1) {
                $requested = $nextFromCounter;
            }

            $query = InvoiceHeaderModel::query()
                ->where('branch_office_id', $branchOfficeId)
                ->where('emission_point_id', $emissionPointId)
                ->where('sequential', str_pad((string) $requested, 9, '0', STR_PAD_LEFT));

            if ($excludeInvoiceId !== null) {
                $query->where('id', '!=', $excludeInvoiceId);
            }

            if (!$query->exists()) {
                return $requested;
            }
        }

        return $nextFromCounter;
    }
}
