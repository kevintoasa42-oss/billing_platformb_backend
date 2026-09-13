<?php

namespace App\Context\V1\Modules\Invoice\Infrastructure\Eloquent\Repositories;

use App\Context\V1\Invoice\Infrastructure\Eloquent\Repositories\InvoiceAdditionalInfo;
use App\Context\V1\Invoice\Infrastructure\Eloquent\Repositories\InvoiceItem;
use App\Context\V1\Invoice\Infrastructure\Eloquent\Repositories\InvoicePayment;
use App\Context\V1\Invoice\Infrastructure\Eloquent\Repositories\InvoiceTax;
use App\Context\V1\Modules\Invoice\Domain\Mappers\InvoiceMapper;
use App\Context\V1\Modules\Invoice\Domain\Models\InvoiceHeader;
use App\Context\V1\Modules\Invoice\Domain\Repositories\InvoiceRepositoryInterface;
use App\Context\V1\Modules\Invoice\Infrastructure\Eloquent\Mappers\EloquentInvoiceMapper;
use App\Models\InvoiceAdditionalInfoModel;
use App\Models\InvoiceHeaderModel;
use App\Models\InvoiceItemModel;
use App\Models\InvoiceItemTaxModel;
use App\Models\InvoicePaymentModel;
use App\Models\InvoiceTaxModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function listPaginated(int $page = 1, int $perPage = 15, ?string $search = null): array
    {
        $query = InvoiceHeaderModel::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('access_key', 'ILIKE', "%{$search}%")
                  ->orWhere('buyer_name', 'ILIKE', "%{$search}%")
                  ->orWhere('buyer_identification', 'ILIKE', "%{$search}%")
                  ->orWhere('ruc', 'ILIKE', "%{$search}%")
                  ->orWhere('legal_name', 'ILIKE', "%{$search}%");
            });
        }

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->with(['items.taxes', 'taxes', 'payments', 'additionalInfo'])
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()
            ->map(fn ($m) => InvoiceMapper::toDtoArray(EloquentInvoiceMapper::toDomain($m)))
            ->toArray();

        return [
            'data' => $data,
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'lastPage' => $paginator->lastPage(),
        ];
    }

    public function getById(int $id): ?array
    {
        $model = InvoiceHeaderModel::with(['items.taxes', 'taxes', 'payments', 'additionalInfo'])->find($id);

        return $model ? InvoiceMapper::toDtoArray(EloquentInvoiceMapper::toDomain($model)) : null;
    }

    public function create(InvoiceHeader $invoice): array
    {
        return DB::connection('tenant')->transaction(function () use ($invoice) {
            $header = InvoiceHeaderModel::create(EloquentInvoiceMapper::toModel($invoice));

            $this->syncItems($header->id, $invoice->items);
            $this->syncTaxes($header->id, $invoice->taxes);
            $this->syncPayments($header->id, $invoice->payments);
            $this->syncAdditionalInfo($header->id, $invoice->additional_info);

            return InvoiceMapper::toDtoArray(EloquentInvoiceMapper::toDomain($header->fresh()));
        });
    }

    public function update(InvoiceHeader $invoice): array
    {
        return DB::connection('tenant')->transaction(function () use ($invoice) {
            $header = InvoiceHeaderModel::findOrFail($invoice->id);
            $header->update(EloquentInvoiceMapper::toModel($invoice));

            // Delete and recreate children
            InvoiceItemModel::where('invoice_header_id', $header->id)->delete();
            InvoiceTaxModel::where('invoice_header_id', $header->id)->delete();
            InvoicePaymentModel::where('invoice_header_id', $header->id)->delete();
            InvoiceAdditionalInfoModel::where('invoice_header_id', $header->id)->delete();

            $this->syncItems($header->id, $invoice->items);
            $this->syncTaxes($header->id, $invoice->taxes);
            $this->syncPayments($header->id, $invoice->payments);
            $this->syncAdditionalInfo($header->id, $invoice->additional_info);

            return InvoiceMapper::toDtoArray(EloquentInvoiceMapper::toDomain($header->fresh()));
        });
    }

    public function changeStatus(int $id, string $status): bool
    {
        return InvoiceHeaderModel::where('id', $id)->update(['status' => $status]) > 0;
    }

    /**
     * @param  InvoiceItem[]  $items
     */
    private function syncItems(int $headerId, array $items): void
    {
        foreach ($items as $item) {
            $itemModel = InvoiceItemModel::create([
                'invoice_header_id' => $headerId,
                'product_id' => $item->product_id,
                'main_code' => $item->main_code,
                'auxiliary_code' => $item->auxiliary_code,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount' => $item->discount,
                'tax_base' => $item->tax_base,
            ]);

            foreach ($item->taxes as $tax) {
                InvoiceItemTaxModel::create([
                    'invoice_item_id' => $itemModel->id,
                    'sri_iva_percentage_id' => $tax->sri_iva_percentage_id,
                    'code' => $tax->code,
                    'percentage_code' => $tax->percentage_code,
                    'rate' => $tax->rate,
                    'tax_base' => $tax->tax_base,
                    'tax' => $tax->tax,
                ]);
            }
        }
    }

    /**
     * @param  InvoiceTax[]  $taxes
     */
    private function syncTaxes(int $headerId, array $taxes): void
    {
        foreach ($taxes as $tax) {
            InvoiceTaxModel::create([
                'invoice_header_id' => $headerId,
                'sri_iva_percentage_id' => $tax->sri_iva_percentage_id,
                'code' => $tax->code,
                'percentage_code' => $tax->percentage_code,
                'rate' => $tax->rate,
                'tax_base' => $tax->tax_base,
                'tax' => $tax->tax,
            ]);
        }
    }

    /**
     * @param  InvoicePayment[]  $payments
     */
    private function syncPayments(int $headerId, array $payments): void
    {
        foreach ($payments as $payment) {
            InvoicePaymentModel::create([
                'invoice_header_id' => $headerId,
                'sri_payment_method_id' => $payment->sri_payment_method_id,
                'payment_code' => $payment->payment_code,
                'total' => $payment->total,
                'term' => $payment->term,
            ]);
        }
    }

    /**
     * @param  InvoiceAdditionalInfo[]  $info
     */
    private function syncAdditionalInfo(int $headerId, array $info): void
    {
        foreach ($info as $item) {
            InvoiceAdditionalInfoModel::create([
                'invoice_header_id' => $headerId,
                'name' => $item->name,
                'value' => $item->value,
            ]);
        }
    }
}
