<?php

namespace App\Context\V1\Modules\Invoice\Infrastructure\Eloquent\Repositories;

use App\Context\V1\Modules\Invoice\Domain\Repositories\SriCatalogRepositoryInterface;
use App\Models\SriIvaPercentageModel;
use App\Models\SriPaymentMethodModel;

class EloquentSriCatalogRepository implements SriCatalogRepositoryInterface
{
    public function getIvaPercentages(): array
    {
        return SriIvaPercentageModel::all()
            ->keyBy('id')
            ->toArray();
    }

    public function getPaymentMethods(): array
    {
        return SriPaymentMethodModel::all()
            ->keyBy('id')
            ->toArray();
    }
}
