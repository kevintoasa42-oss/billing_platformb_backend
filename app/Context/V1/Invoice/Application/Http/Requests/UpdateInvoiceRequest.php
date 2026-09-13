<?php

namespace App\Context\V1\Invoice\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'access_key' => 'sometimes|required|string|size:49',
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
            'subtotal' => 'sometimes|required|numeric|min:0',
            'discount' => 'sometimes|numeric|min:0',
            'tax_base' => 'sometimes|required|numeric|min:0',
            'tax' => 'sometimes|numeric|min:0',
            'tip' => 'sometimes|numeric|min:0',
            'total' => 'sometimes|required|numeric|min:0',
            'currency' => 'sometimes|string|max:10',
            'plate' => 'sometimes|nullable|string|max:20',
            'status' => 'sometimes|string|in:PENDIENTE,RECHAZADO,AUTORIZADO',
            'items' => 'sometimes|array',
            'items.*.product_id' => 'nullable|integer|exists:tenant.products,id',
            'items.*.main_code' => 'required_with:items|string|max:100',
            'items.*.auxiliary_code' => 'nullable|string|max:100',
            'items.*.description' => 'required_with:items|string',
            'items.*.quantity' => 'required_with:items|numeric|min:0',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.total_without_tax' => 'required_with:items|numeric|min:0',
            'items.*.taxes' => 'nullable|array',
            'items.*.taxes.*.sri_iva_percentage_id' => 'nullable|integer|exists:pgsql.sri_iva_percentages,id',
            'items.*.taxes.*.code' => 'required_with:items.*.taxes|string|max:2',
            'items.*.taxes.*.percentage_code' => 'required_with:items.*.taxes|string|max:2',
            'items.*.taxes.*.rate' => 'required_with:items.*.taxes|numeric|min:0',
            'items.*.taxes.*.tax_base' => 'required_with:items.*.taxes|numeric|min:0',
            'items.*.taxes.*.tax' => 'required_with:items.*.taxes|numeric|min:0',
            'taxes' => 'sometimes|array',
            'taxes.*.sri_iva_percentage_id' => 'nullable|integer|exists:pgsql.sri_iva_percentages,id',
            'taxes.*.code' => 'required_with:taxes|string|max:2',
            'taxes.*.percentage_code' => 'required_with:taxes|string|max:2',
            'taxes.*.rate' => 'required_with:taxes|numeric|min:0',
            'taxes.*.tax_base' => 'required_with:taxes|numeric|min:0',
            'taxes.*.tax' => 'required_with:taxes|numeric|min:0',
            'payments' => 'sometimes|array',
            'payments.*.sri_payment_method_id' => 'required_with:payments|integer|exists:pgsql.sri_payment_methods,id',
            'payments.*.total' => 'required_with:payments|numeric|min:0',
            'payments.*.total' => 'required_with:payments|numeric|min:0',
            'payments.*.term' => 'nullable|integer|min:0',
            'additional_info' => 'sometimes|array',
            'additional_info.*.name' => 'required_with:additional_info|string|max:100',
            'additional_info.*.value' => 'nullable|string',
        ];
    }

    public static function toDTO(array $data): \App\Context\V1\Invoice\Application\DTOs\InvoiceDTO
    {
        return \App\Context\V1\Invoice\Application\DTOs\InvoiceDTO::fromArray($data);
    }
}
