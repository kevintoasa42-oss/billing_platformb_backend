<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Domain\Repository;

/**
 * Canonical carrier projection and commands.
 *
 * The carrier aggregate is addressed by its ThirdParty UUID. Legacy numeric
 * identifiers are deliberately absent from this port.
 */
interface CarrierProfileRepositoryInterface
{
    /** @return list<array<string,mixed>> */
    public function list(string $tenantId, array $filters = []): array;

    /** @return array<string,mixed>|null */
    public function findByThirdPartyId(string $tenantId, string $thirdPartyId): ?array;

    /** @return array<string,mixed> */
    public function onboard(string $tenantId, array $input, string $idempotencyKey, ?string $actorId = null): array;

    /** @return array<string,mixed> */
    public function update(string $tenantId, string $thirdPartyId, array $input, ?string $actorId = null): array;

    /** @return array<string,mixed> */
    public function updatePaymentAccount(string $tenantId, string $thirdPartyId, array $input, ?string $actorId = null): array;

    /** @return array<string,mixed> */
    public function updateSignature(string $tenantId, string $thirdPartyId, array $input, ?string $actorId = null): array;

    /** @return array<string,mixed> */
    public function recordDocument(string $tenantId, string $thirdPartyId, array $input, ?string $actorId = null): array;

    /** @return array<string,mixed> */
    public function cancelDocument(string $tenantId, string $thirdPartyId, string $documentId, string $reason, ?string $actorId = null): array;

    /** @return array<string,mixed> */
    public function allocateDocument(string $tenantId, string $thirdPartyId, array $input, string $idempotencyKey, ?string $actorId = null): array;

    /** @return array<string,mixed> */
    public function reverseAllocation(string $tenantId, string $thirdPartyId, string $allocationId, string $reason, ?string $actorId = null): array;

    /** @return array<string,mixed> */
    public function clearSettlementReview(string $tenantId, string $thirdPartyId, string $operationId, string $reason, ?string $actorId = null): array;

    /** @return list<array<string,mixed>> */
    public function auditTrail(string $tenantId, string $thirdPartyId): array;
}
