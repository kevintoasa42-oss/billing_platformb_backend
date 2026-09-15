<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Postgres;

use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\Invoice;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models\DocumentArtifactModel;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models\DocumentModel;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Mappers\InvoiceMapper;
use App\Context\V3\Modules\Fiscal\Worker\Domain\Models\ProcessingJob;
use App\Context\V3\Modules\Fiscal\Worker\Domain\Ports\FiscalPipelineInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrates the full drain: claim -> process -> store artifact -> record event -> complete.
 */
final class FiscalWorkerRepository
{
    public function __construct(
        private readonly ProcessingJobRepository $jobRepository,
        private readonly FiscalPipelineInterface $pipeline,
        private readonly InvoiceMapper $invoiceMapper,
    ) {}

    public function drain(string $tenantId, int $limit = 100): int
    {
        $processed = 0;

        while ($processed < $limit) {
            $job = $this->jobRepository->claimNext($tenantId);
            if ($job === null) {
                break;
            }

            try {
                $this->processJob($tenantId, $job);
                $this->jobRepository->complete($tenantId, $job->id);
                $processed++;
            } catch (\Throwable $e) {
                Log::error('Worker job failed', [
                    'job_id' => $job->id,
                    'operation' => $job->operation,
                    'error' => $e->getMessage(),
                ]);
                $retryable = $job->attempts < $job->maxAttempts;
                $this->jobRepository->fail($tenantId, $job->id, $e->getMessage(), $retryable);
            }
        }

        return $processed;
    }

    private function processJob(string $tenantId, ProcessingJob $job): void
    {
        // Set tenant context for RLS
        DB::connection('master_v3')->statement(
            "SELECT set_config('app.tenant_id', ?, false)",
            [$tenantId],
        );

        $documentModel = DocumentModel::on('master_v3')
            ->where('tenant_id', $tenantId)
            ->where('id', $job->documentId)
            ->with(['lines', 'payments'])
            ->first();

        if ($documentModel === null) {
            throw new \RuntimeException("Document not found: {$job->documentId}");
        }

        $invoice = $this->invoiceMapper->toDomain($documentModel);
        $enterprise = $documentModel->issuer_snapshot ?? [];

        $result = $this->pipeline->process($tenantId, $job->operation, $invoice, [
            'enterprise' => $enterprise,
            'document' => $documentModel,
        ]);

        if (isset($result['artifact'])) {
            $this->storeArtifact($tenantId, $job->documentId, $result['artifact']);
        }

        if (isset($result['status'])) {
            $this->updateDocumentStatus($tenantId, $job->documentId, $result['status']);
        }

        $this->recordEvent($tenantId, $job->documentId, $job->operation, $result);
    }

    /** @param array{kind: string, content: string, content_type: string} $artifact */
    private function storeArtifact(string $tenantId, string $documentId, array $artifact): void
    {
        DB::connection('master_v3')->statement(
            "SELECT set_config('app.tenant_id', ?, false)",
            [$tenantId],
        );

        $sha256 = hash('sha256', $artifact['content']);
        $content = $artifact['content'];

        // Use pg_escape_bytea for binary content (PDF, etc.)
        $pdo = DB::connection('master_v3')->getPdo();
        $escapedContent = pg_escape_bytea($content);

        DB::connection('master_v3')->statement(
            "INSERT INTO integration.document_artifacts (tenant_id, document_id, kind, content, sha256)
             VALUES (?, ?, ?, ?::bytea, ?)
             ON CONFLICT (tenant_id, document_id, kind) DO UPDATE
             SET content = EXCLUDED.content, sha256 = EXCLUDED.sha256",
            [$tenantId, $documentId, $artifact['kind'], $escapedContent, $sha256],
        );
    }

    private function updateDocumentStatus(string $tenantId, string $documentId, string $status): void
    {
        DB::connection('master_v3')->statement(
            "SELECT set_config('app.tenant_id', ?, false)",
            [$tenantId],
        );
        DB::connection('master_v3')->statement(
            "UPDATE fiscal.documents SET fiscal_status = ? WHERE tenant_id = ? AND id = ?",
            [$status, $tenantId, $documentId],
        );
    }

    /** @param array<string, mixed> $result */
    private function recordEvent(string $tenantId, string $documentId, string $operation, array $result): void
    {
        DB::connection('master_v3')->statement(
            "SELECT set_config('app.tenant_id', ?, false)",
            [$tenantId],
        );
        DB::connection('master_v3')->statement(
            "INSERT INTO fiscal.document_events (tenant_id, document_id, event, created_at)
             VALUES (?, ?, ?, now())",
            [$tenantId, $documentId, "fiscal.{$operation}"],
        );
    }
}
