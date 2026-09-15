<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Postgres;

use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\Invoice;
use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\InvoiceLine;
use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\InvoicePayment;
use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\InvoiceReadiness;
use App\Context\V3\Modules\Core\Establishment\Domain\Repository\EmissionPointRepositoryInterface;
use App\Context\V3\Modules\Fiscal\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models\DocumentArtifactModel;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models\DocumentModel;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models\InvoiceSubtypeModel;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models\OperationModel;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models\TenantDataRouteModel;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Mappers\InvoiceMapper;
use App\Context\V3\Modules\Fiscal\Worker\Application\UseCases\DispatchInvoiceJobsUseCase;
use App\Context\V3\Modules\Fiscal\Sri\Domain\Services\SriAccessKeyGenerator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

final class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function __construct(
        private readonly InvoiceMapper $mapper,
        private readonly DispatchInvoiceJobsUseCase $dispatchJobsUseCase,
        private readonly EmissionPointRepositoryInterface $emissionPoints,
    ) {}

    public function all(array $filters = []): array
    {
        $query = DocumentModel::query()
            ->where('document_type', 'invoice')
            ->orderBy('issued_at', 'desc');

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('fiscal_status', $filters['status']);
        }

        $perPage = (int) ($filters['per_page'] ?? 15);
        $page = (int) ($filters['page'] ?? 1);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $items = $paginator->getCollection()->map(fn (DocumentModel $m): Invoice => $this->mapper->toDomain($m))->all();

        return [
            'items' => $items,
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    public function findByLegacyId(int $legacyId): ?Invoice
    {
        $record = DocumentModel::query()
            ->where('document_type', 'invoice')
            ->where('legacy_id', $legacyId)
            ->first();

        if ($record === null) {
            return null;
        }

        $record->load(['lines', 'payments']);

        return $this->mapper->toDomain($record);
    }

    public function create(array $input, string $idempotencyKey, string $actorId): Invoice
    {
        return DB::connection('master_v3')->transaction(function () use ($input, $idempotencyKey, $actorId): Invoice {
            $sequential = $this->reserveSequential(
                (string) $input['emission_point_id'],
                'invoice',
            );

            $tenantId = (string) ($input['tenant_id'] ?? DB::selectOne('SELECT auth.tenant_id() AS id')?->id ?? '');

            $route = DB::selectOne(
                'SELECT writer_epoch FROM platform.tenant_data_routes WHERE tenant_id = ? FOR SHARE',
                [$tenantId],
            );
            $writerEpoch = $route?->writer_epoch ?? 1;

            $accessKey = $input['access_key'] ?? $this->generateAccessKey($input, $sequential);

            $operation = OperationModel::query()->create([
                'document_id' => Str::uuid()->toString(),
                'actor_id' => $actorId !== '' ? $actorId : null,
                'action' => 'issue',
                'status' => 'completed',
                'idempotency_key' => $idempotencyKey,
            ]);

            $document = DocumentModel::query()->create([
                'environment' => 'lab',
                'document_type' => 'invoice',
                'emission_point_id' => $input['emission_point_id'],
                'establishment_code' => $input['establishment_code'],
                'emission_point_code' => $input['emission_point_code'],
                'sequential' => $sequential,
                'access_key' => $accessKey,
                'authorization_number' => null,
                'issued_at' => $input['issued_at'] ?? now(),
                'issuer_snapshot' => $input['issuer_snapshot'],
                'recipient_snapshot' => $input['recipient_snapshot'],
                'subtotal' => $input['subtotal'],
                'tax' => $input['tax'],
                'discount' => $input['discount'] ?? 0,
                'total' => $input['total'],
                'fiscal_status' => 'simulated',
                'collection_status' => 'pending',
                'delivery_status' => 'pending',
                'due_at' => $input['due_at'] ?? null,
                'operation_id' => $operation->id,
                'carrier_id' => null,
                'vehicle_id' => null,
                'plate_snapshot' => null,
                'writer_epoch' => $writerEpoch,
                'not_valid_for_sri' => false,
                'fiscal_status_summary' => 'simulada',
            ]);

            $operation->update(['document_id' => $document->id]);

            InvoiceSubtypeModel::query()->create([
                'id' => $document->id,
            ]);

            foreach ($input['lines'] ?? [] as $lineData) {
                $document->lines()->create([
                    'product_id' => $lineData['product_id'] ?? null,
                    'description_snapshot' => $lineData['description_snapshot'] ?? null,
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'subtotal' => $lineData['subtotal'],
                    'discount' => $lineData['discount'] ?? 0,
                    'tax' => $lineData['tax'] ?? 0,
                    'total' => $lineData['total'] ?? $lineData['subtotal'],
                    'taxable_base' => $lineData['taxable_base'] ?? null,
                    'sri_principal_code' => $lineData['sri_principal_code'] ?? null,
                ]);
            }

            foreach ($input['payments'] ?? [] as $index => $paymentData) {
                $document->payments()->create([
                    'position' => $index + 1,
                    'payment_method_code' => $paymentData['payment_method_code'],
                    'total' => $paymentData['total'],
                    'term' => $paymentData['term'] ?? null,
                    'time_unit' => $paymentData['time_unit'] ?? null,
                    'due_date' => $paymentData['due_date'] ?? null,
                ]);
            }

            $document->refresh();
            $document->load(['lines', 'payments']);

            $this->dispatchJobsUseCase->execute($tenantId, (string) $document->id, $writerEpoch);

            return $this->mapper->toDomain($document);
        });
    }

    /**
     * Generate the SRI access key (49 digits) for the document.
     *
     * @param  array<string, mixed>  $input
     */
    private function generateAccessKey(array $input, int $sequential): string
    {
        $issueDate = (string) ($input['issued_at'] ?? now()->format('Y-m-d'));
        $ruc = (string) ($input['issuer_snapshot']['ruc'] ?? '0000000000000');
        $environmentCode = (string) (config('services.sri.mode') === 'sri' ? '2' : '1');
        $establishment = (string) ($input['establishment_code'] ?? '001');
        $emissionPoint = (string) ($input['emission_point_code'] ?? '001');
        $sequentialStr = str_pad((string) $sequential, 9, '0', STR_PAD_LEFT);

        return SriAccessKeyGenerator::generate(
            $issueDate,
            $ruc,
            $environmentCode,
            $establishment,
            $emissionPoint,
            $sequentialStr,
        );
    }

    public function authorize(int $legacyId): ?Invoice
    {
        $record = DocumentModel::query()
            ->where('document_type', 'invoice')
            ->where('legacy_id', $legacyId)
            ->first();

        if ($record === null) {
            return null;
        }

        $record->update([
            'fiscal_status' => 'authorized',
            'authorization_number' => 'MOCK-'.str_pad((string) $record->sequential, 9, '0', STR_PAD_LEFT),
            'fiscal_status_summary' => 'autorizada',
        ]);

        OperationModel::query()->create([
            'document_id' => $record->id,
            'actor_id' => null,
            'action' => 'authorize',
            'status' => 'completed',
            'idempotency_key' => Str::uuid()->toString(),
        ]);

        $record->refresh();
        $record->load(['lines', 'payments']);

        return $this->mapper->toDomain($record);
    }

    public function void(int $legacyId, string $reasonCode, string $reasonNote): ?Invoice
    {
        $record = DocumentModel::query()
            ->where('document_type', 'invoice')
            ->where('legacy_id', $legacyId)
            ->first();

        if ($record === null) {
            return null;
        }

        $record->update([
            'fiscal_status' => 'voided',
            'fiscal_status_summary' => 'anulada',
        ]);

        OperationModel::query()->create([
            'document_id' => $record->id,
            'actor_id' => null,
            'action' => 'void',
            'status' => 'completed',
            'idempotency_key' => Str::uuid()->toString(),
        ]);

        $record->refresh();
        $record->load(['lines', 'payments']);

        return $this->mapper->toDomain($record);
    }

    public function summary(array $filters = []): array
    {
        $rows = DB::connection('master_v3')->select(<<<'SQL'
            SELECT
                COUNT(*) FILTER (WHERE fiscal_status = 'simulated') AS simulated,
                COUNT(*) FILTER (WHERE fiscal_status = 'authorized') AS authorized,
                COUNT(*) FILTER (WHERE fiscal_status = 'voided') AS voided,
                COUNT(*) FILTER (WHERE fiscal_status = 'rejected') AS rejected,
                COALESCE(SUM(total) FILTER (WHERE fiscal_status = 'authorized'), 0) AS authorized_total,
                COALESCE(SUM(total) FILTER (WHERE fiscal_status = 'simulated'), 0) AS simulated_total
            FROM fiscal.documents
            WHERE document_type = 'invoice'
        SQL);

        $row = $rows[0] ?? (object) [
            'simulated' => 0, 'authorized' => 0, 'voided' => 0, 'rejected' => 0,
            'authorized_total' => 0, 'simulated_total' => 0,
        ];

        return [
            'simulated' => (int) $row->simulated,
            'authorized' => (int) $row->authorized,
            'voided' => (int) $row->voided,
            'rejected' => (int) $row->rejected,
            'authorized_total' => (string) $row->authorized_total,
            'simulated_total' => (string) $row->simulated_total,
        ];
    }

    public function export(array $filters = []): string
    {
        $invoices = DocumentModel::query()
            ->where('document_type', 'invoice')
            ->orderBy('issued_at', 'desc')
            ->limit(1000)
            ->get();

        $header = ['id', 'document_number', 'issued_at', 'fiscal_status', 'subtotal', 'tax', 'total'];
        $lines = [implode(',', $header)];

        foreach ($invoices as $invoice) {
            $lines[] = implode(',', [
                $invoice->legacy_id,
                $invoice->establishment_code.'-'.$invoice->emission_point_code.'-'.str_pad((string) $invoice->sequential, 9, '0', STR_PAD_LEFT),
                $invoice->issued_at,
                $invoice->fiscal_status,
                $invoice->subtotal,
                $invoice->tax,
                $invoice->total,
            ]);
        }

        return implode("\n", $lines);
    }

    public function readiness(int $branchId, int $issuancePointId): InvoiceReadiness
    {
        $blockers = [];

        $route = TenantDataRouteModel::query()->first();
        if ($route !== null && $route->frozen) {
            $blockers[] = 'tenant_data_frozen';
        }

        // The editor passes the legacy numeric ids of the branch and the
        // emission point; the establishment repository resolves the next
        // sequential from fiscal.sequences and formats the document number.
        $sequential = $this->emissionPoints->nextSequential($branchId, $issuancePointId);

        return new InvoiceReadiness(
            ready: $blockers === [],
            mode: 'mock',
            blockers: $blockers,
            sequential: $sequential,
        );
    }

    public function artifact(int $legacyId, string $kind): ?string
    {
        $document = DocumentModel::query()
            ->where('document_type', 'invoice')
            ->where('legacy_id', $legacyId)
            ->first();

        if ($document === null) {
            return null;
        }

        $artifact = DocumentArtifactModel::query()
            ->where('document_id', $document->id)
            ->where('kind', $kind)
            ->first();

        if ($artifact === null) {
            return null;
        }

        return (string) $artifact->content;
    }

    /**
     * Reserve and increment the next sequential number for the given emission point.
     */
    private function reserveSequential(string $emissionPointId, string $documentType): int
    {
        $row = DB::connection('master_v3')->selectOne(<<<'SQL'
            UPDATE fiscal.sequences
               SET last_number = last_number + 1
             WHERE emission_point_id = ?
               AND document_type = ?
             RETURNING last_number
        SQL, [$emissionPointId, $documentType]);

        if ($row === null) {
            DB::connection('master_v3')->statement(<<<'SQL'
                INSERT INTO fiscal.sequences (tenant_id, environment, emission_point_id, document_type, last_number)
                SELECT current_setting('app.tenant_id', true)::uuid, 'lab', ?, ?, 1
            SQL, [$emissionPointId, $documentType]);

            return 1;
        }

        return (int) $row->last_number;
    }

    /** @return array<string, mixed> */
    public function editorContext(string $tenantId): array
    {
        DB::connection('master_v3')->statement(
            "SELECT set_config('app.tenant_id', ?, false)",
            [$tenantId],
        );

        $company = DB::connection('master_v3')->selectOne(
            'SELECT id, legal_name, trade_name, ruc FROM core.companies WHERE tenant_id = ? LIMIT 1',
            [$tenantId],
        );

        $tenant = DB::connection('master_v3')->selectOne(
            'SELECT legacy_id FROM platform.tenants WHERE id = ? LIMIT 1',
            [$tenantId],
        );
        $enterpriseLegacyId = $tenant !== null ? (int) $tenant->legacy_id : 0;

        $branches = DB::connection('master_v3')->select(
            'SELECT id, legacy_id, sri_code, name, address, is_active FROM core.establishments WHERE tenant_id = ? ORDER BY sri_code',
            [$tenantId],
        );

        $issuancePoints = DB::connection('master_v3')->select(
            'SELECT ep.id, ep.legacy_id, ep.sri_code, ep.name, ep.is_active, ep.is_default, ep.has_tax_validity, e.sri_code AS establishment_code, e.legacy_id AS establishment_legacy_id FROM core.emission_points ep JOIN core.establishments e ON ep.establishment_id = e.id WHERE ep.tenant_id = ? ORDER BY ep.sri_code',
            [$tenantId],
        );

        $sequences = DB::connection('master_v3')->select(
            'SELECT emission_point_id, document_type, last_number FROM fiscal.sequences WHERE tenant_id = ?',
            [$tenantId],
        );

        // Build emissionConfig: pick the first active branch with an active
        // emission point that has tax validity, then resolve its next invoice
        // sequential from fiscal.sequences (defaults to 1 when no row exists).
        $emissionConfig = null;
        $sequenceByPoint = [];
        foreach ($sequences as $seq) {
            if ($seq->document_type === 'invoice') {
                $sequenceByPoint[$seq->emission_point_id] = (int) $seq->last_number;
            }
        }
        foreach ($branches as $branch) {
            if (! $branch->is_active) continue;
            foreach ($issuancePoints as $ep) {
                if ($ep->establishment_code !== $branch->sri_code) continue;
                if (! $ep->is_active || ! $ep->has_tax_validity) continue;

                $last = $sequenceByPoint[$ep->id] ?? 0;
                $next = $last + 1;
                $emissionConfig = [
                    'label' => $branch->sri_code.'-'.$ep->sri_code.' · '.$branch->name,
                    'name' => $branch->name,
                    'ready' => true,
                    'blocker' => '',
                    'nextSequential' => sprintf('%s-%s-%09d', $branch->sri_code, $ep->sri_code, $next),
                    'branchId' => (int) $branch->legacy_id,
                    'issuancePointId' => (int) $ep->legacy_id,
                ];
                break 2;
            }
        }
        if ($emissionConfig === null) {
            $emissionConfig = [
                'label' => 'Configura sucursal y punto de emisión',
                'name' => '',
                'ready' => false,
                'blocker' => 'Configura una sucursal y un punto de emisión activos.',
                'nextSequential' => '',
                'branchId' => null,
                'issuancePointId' => null,
            ];
        }

        return [
            'company' => $company ? [
                'id' => $company->id,
                'legal_name' => $company->legal_name,
                'trade_name' => $company->trade_name,
                'ruc' => $company->ruc,
            ] : null,
            'catalogScope' => [
                'enterpriseId' => $enterpriseLegacyId,
                'version' => 1,
            ],
            'branches' => array_map(static fn ($b): array => [
                'id' => $b->id,
                'sri_code' => $b->sri_code,
                'name' => $b->name,
                'address' => $b->address,
                'is_active' => $b->is_active,
            ], $branches),
            'issuance_points' => array_map(static fn ($ep): array => [
                'id' => $ep->id,
                'sri_code' => $ep->sri_code,
                'name' => $ep->name,
                'is_active' => $ep->is_active,
                'establishment_code' => $ep->establishment_code,
            ], $issuancePoints),
            'sequences' => array_map(static fn ($s): array => [
                'emission_point_id' => $s->emission_point_id,
                'document_type' => $s->document_type,
                'last_number' => $s->last_number,
            ], $sequences),
            'emissionConfig' => $emissionConfig,
        ];
    }
}
