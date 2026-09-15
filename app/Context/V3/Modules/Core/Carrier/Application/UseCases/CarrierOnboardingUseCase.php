<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Application\UseCases;

use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierProfileRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\ValueObjects\CanonicalIdentification;
use DomainException;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/** Application boundary for the canonical ThirdParty + Carrier experience. */
final class CarrierOnboardingUseCase
{
    public function __construct(
        private readonly CarrierProfileRepositoryInterface $repository,
    ) {}

    /** @return list<array<string,mixed>> */
    public function list(string $tenantId, array $filters = []): array
    {
        return $this->call(fn (): array => $this->repository->list($tenantId, $filters));
    }

    /** @return array<string,mixed> */
    public function onboard(string $tenantId, array $input, string $idempotencyKey, ?string $actorId = null): array
    {
        if (! preg_match('/^[A-Za-z0-9_-]{8,100}$/D', trim($idempotencyKey))) {
            throw new BadRequestHttpException('La cabecera Idempotency-Key debe contener entre 8 y 100 caracteres.');
        }

        try {
            $identity = CanonicalIdentification::from(
                isset($input['identification_type']) ? (string) $input['identification_type'] : null,
                isset($input['identification']) ? (string) $input['identification'] : null,
            );
        } catch (InvalidArgumentException $error) {
            throw new UnprocessableEntityHttpException($error->getMessage(), $error);
        }

        if ($identity === null) {
            throw new UnprocessableEntityHttpException('La identificación fiscal es obligatoria.');
        }

        $input['identification'] = $identity->value;
        $input['identification_type'] = $identity->type;
        if (array_key_exists('issuer_mode', $input) && $input['issuer_mode'] !== null) {
            $input['issuer_mode'] = strtolower(trim((string) $input['issuer_mode']));
            if (! in_array($input['issuer_mode'], ['operator', 'partner'], true)) {
                throw new UnprocessableEntityHttpException('El modo de emisor debe ser operator o partner.');
            }
        }

        return $this->call(fn (): array => $this->repository->onboard($tenantId, $input, trim($idempotencyKey), $actorId));
    }

    /** @return array<string,mixed> */
    public function show(string $tenantId, string $thirdPartyId): array
    {
        $result = $this->call(fn (): ?array => $this->repository->findByThirdPartyId($tenantId, $thirdPartyId));
        if ($result === null) {
            throw new NotFoundHttpException('No se encontró el socio transportista.');
        }

        return $result;
    }

    /** @return array<string,mixed> */
    public function update(string $tenantId, string $thirdPartyId, array $input, ?string $actorId = null): array
    {
        return $this->call(fn (): array => $this->repository->update($tenantId, $thirdPartyId, $input, $actorId));
    }

    /** @return array<string,mixed> */
    public function updatePaymentAccount(string $tenantId, string $thirdPartyId, array $input, ?string $actorId = null): array
    {
        return $this->call(fn (): array => $this->repository->updatePaymentAccount($tenantId, $thirdPartyId, $input, $actorId));
    }

    /** @return array<string,mixed> */
    public function updateSignature(string $tenantId, string $thirdPartyId, array $input, ?string $actorId = null): array
    {
        return $this->call(fn (): array => $this->repository->updateSignature($tenantId, $thirdPartyId, $input, $actorId));
    }

    /** @return array<string,mixed> */
    public function recordDocument(string $tenantId, string $thirdPartyId, array $input, ?string $actorId = null): array
    {
        return $this->call(fn (): array => $this->repository->recordDocument($tenantId, $thirdPartyId, $input, $actorId));
    }

    /** @return array<string,mixed> */
    public function cancelDocument(string $tenantId, string $thirdPartyId, string $documentId, string $reason, ?string $actorId = null): array
    {
        return $this->call(fn (): array => $this->repository->cancelDocument($tenantId, $thirdPartyId, $documentId, $reason, $actorId));
    }

    /** @return array<string,mixed> */
    public function allocateDocument(string $tenantId, string $thirdPartyId, array $input, string $idempotencyKey, ?string $actorId = null): array
    {
        return $this->call(fn (): array => $this->repository->allocateDocument($tenantId, $thirdPartyId, $input, $idempotencyKey, $actorId));
    }

    /** @return array<string,mixed> */
    public function reverseAllocation(string $tenantId, string $thirdPartyId, string $allocationId, string $reason, ?string $actorId = null): array
    {
        return $this->call(fn (): array => $this->repository->reverseAllocation($tenantId, $thirdPartyId, $allocationId, $reason, $actorId));
    }

    /** @return array<string,mixed> */
    public function clearSettlementReview(string $tenantId, string $thirdPartyId, string $operationId, string $reason, ?string $actorId = null): array
    {
        return $this->call(fn (): array => $this->repository->clearSettlementReview($tenantId, $thirdPartyId, $operationId, $reason, $actorId));
    }

    /** @return list<array<string,mixed>> */
    public function audit(string $tenantId, string $thirdPartyId): array
    {
        return $this->call(fn (): array => $this->repository->auditTrail($tenantId, $thirdPartyId));
    }

    /** @template T */
    private function call(\Closure $operation): mixed
    {
        try {
            return $operation();
        } catch (DomainException $error) {
            throw new UnprocessableEntityHttpException($error->getMessage(), $error);
        }
    }
}
