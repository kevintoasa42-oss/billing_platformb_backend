<?php

namespace App\Context\V1\SriVoucherTypes\Domain\Repositories;

use App\Context\V1\SriVoucherTypes\Domain\Models\SriVoucherType;

interface SriVoucherTypeRepositoryInterface
{
    /** @return array{data: SriVoucherType[], total: int, page: int, perPage: int, lastPage: int} */
    public function listPaginated(int $page = 1, int $perPage = 15, array $filters = []): array;

    public function findById(int $id): ?SriVoucherType;

    /** Returns the voucher type active today for an SRI document code. */
    public function findCurrentByCode(string $code): ?SriVoucherType;

    public function create(SriVoucherType $voucherType): SriVoucherType;

    public function update(SriVoucherType $voucherType): SriVoucherType;

    public function delete(int $id): bool;
}
