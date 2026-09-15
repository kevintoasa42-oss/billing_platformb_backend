<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Domain\Models;

/**
 * Plain PHP domain model for a processing job.
 */
final class ProcessingJob
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public const OP_XML = 'xml';
    public const OP_SIGNATURE = 'signature';
    public const OP_SEND = 'send';
    public const OP_AUTHORIZATION = 'authorization';
    public const OP_AUTHORIZED_XML = 'authorized_xml';
    public const OP_PDF = 'pdf';
    public const OP_DELIVERY = 'delivery';

    /** @var list<string> */
    public const OPERATIONS = [
        self::OP_XML,
        self::OP_SIGNATURE,
        self::OP_SEND,
        self::OP_AUTHORIZATION,
        self::OP_AUTHORIZED_XML,
        self::OP_PDF,
        self::OP_DELIVERY,
    ];

    public function __construct(
        public readonly ?string $id,
        public readonly string $tenantId,
        public readonly string $documentId,
        public readonly string $operation,
        public readonly ?string $predecessorId,
        public string $status,
        public readonly ?string $availableAt,
        public readonly ?string $leaseUntil,
        public readonly ?string $fencingToken,
        public readonly int $writerEpoch,
        public int $attempts,
        public readonly int $maxAttempts,
    ) {}
}
