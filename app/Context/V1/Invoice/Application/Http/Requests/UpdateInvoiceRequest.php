<?php

namespace App\Context\V1\Invoice\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\InvoiceHeaderModel;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'carrier_id' => 'sometimes|nullable|integer|exists:tenant.carriers,id',
            'environment' => 'sometimes|string|in:1,2',
            'emission_type' => 'sometimes|string|in:1,2',
            'ruc' => 'sometimes|required|string|max:13',
            'legal_name' => 'sometimes|required|string|max:255',
            'tradename' => 'sometimes|nullable|string|max:255',
            'access_key' => 'sometimes|nullable|string|size:49',
            'document_code' => 'sometimes|string|max:2',
            'establishment' => 'sometimes|required|string|max:3',
            'emission_point' => 'sometimes|required|string|max:3',
            'sequential' => 'sometimes|required|string|max:9',
            'matrix_address' => 'sometimes|nullable|string|max:500',
            'issue_date' => 'sometimes|required|date',
            'establishment_address' => 'sometimes|nullable|string|max:500',
            'accounting_required' => 'sometimes|string|in:SI,NO',
            'buyer_identification_type' => 'sometimes|required|string|max:2',
            'buyer_name' => 'sometimes|required|string|max:255',
            'buyer_identification' => 'sometimes|required|string|max:20',
            'buyer_address' => 'sometimes|nullable|string|max:500',
            'buyer_phone' => 'sometimes|nullable|string|max:20',
            'buyer_email' => 'sometimes|nullable|email|max:255',
            'tip' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|max:10',
            'plate' => 'sometimes|nullable|string|max:20',
            'status' => 'sometimes|string|in:PENDIENTE,RECHAZADO,AUTORIZADO',
            'items' => 'sometimes|array',
            'items.*.product_id' => 'nullable|integer|exists:tenant.products,id',
            'items.*.main_code' => 'required_with:items|string|max:100',
            'items.*.auxiliary_code' => 'nullable|string|max:100',
            'items.*.description' => 'required_with:items|string',
            'items.*.quantity' => 'required_with:items|numeric|min:0.0001',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.taxes' => 'nullable|array',
            'items.*.taxes.*.sri_iva_percentage_id' => 'required|integer|exists:pgsql.sri_iva_percentages,id',
            'items.*.taxes.*.tax_base' => 'required_with:items.*.taxes|numeric|min:0',
            'items.*.taxes.*.tax' => 'required_with:items.*.taxes|numeric|min:0',
            'payments' => 'sometimes|array',
            'payments.*.sri_payment_method_id' => 'required_with:payments|integer|exists:pgsql.sri_payment_methods,id',
            'payments.*.total' => 'required_with:payments|numeric|min:0',
            'payments.*.term' => 'nullable|integer|min:0',
            'additional_info' => 'sometimes|array',
            'additional_info.*.name' => 'required_with:additional_info|string|max:100',
            'additional_info.*.value' => 'nullable|string',
        ];
    }

    /**
     * Validate that the sequential is unique (excluding the current invoice).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $establishment = $this->input('establishment');
            $emissionPoint = $this->input('emission_point');
            $sequential = $this->input('sequential');
            $invoiceId = $this->route('id');

            if ($establishment && $emissionPoint && $sequential) {
                $exists = InvoiceHeaderModel::where('establishment', $establishment)
                    ->where('emission_point', $emissionPoint)
                    ->where('sequential', $sequential)
                    ->where('id', '!=', $invoiceId)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        'sequential',
                        'The sequential number already exists for this establishment and emission point.'
                    );
                }
            }
        });
    }

    public static function toDTO(array $data): \App\Context\V1\Invoice\Application\DTOs\InvoiceDTO
    {
        return \App\Context\V1\Invoice\Application\DTOs\InvoiceDTO::fromArray($data);
    }
}
