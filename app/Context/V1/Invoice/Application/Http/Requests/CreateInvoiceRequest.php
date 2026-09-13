<?php

namespace App\Context\V1\Invoice\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'carrier_id' => 'nullable|integer|exists:tenant.carriers,id',
            'branch_office_id' => 'required|integer|exists:tenant.branch_offices,id',
            'emission_point_id' => 'required|integer|exists:tenant.emission_points,id',
            'environment' => 'prohibited',
            'emission_type' => 'prohibited',
            'ruc' => 'required|string|max:13',
            'legal_name' => 'required|string|max:255',
            'tradename' => 'nullable|string|max:255',
            'access_key' => 'prohibited',
            'document_code' => 'nullable|string|max:2',
            'establishment' => 'prohibited',
            'emission_point' => 'prohibited',
            'sequential' => 'nullable|string|max:9',
            'matrix_address' => 'nullable|string|max:500',
            'issue_date' => 'required|date',
            'establishment_address' => 'nullable|string|max:500',
            'accounting_required' => 'nullable|string|in:SI,NO',
            'buyer_identification_type' => 'required|string|max:2',
            'buyer_name' => 'required|string|max:255',
            'buyer_identification' => 'required|string|max:20',
            'buyer_address' => 'nullable|string|max:500',
            'buyer_phone' => 'nullable|string|max:20',
            'buyer_email' => 'nullable|email|max:255',
            'tip' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'plate' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer|exists:tenant.products,id',
            'items.*.main_code' => 'required|string|max:100',
            'items.*.auxiliary_code' => 'nullable|string|max:100',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.taxes' => 'nullable|array',
            'items.*.taxes.*.sri_iva_percentage_id' => 'required|integer|exists:pgsql.sri_iva_percentages,id',
            'items.*.taxes.*.tax_base' => 'required|numeric|min:0',
            'items.*.taxes.*.tax' => 'required|numeric|min:0',
            'payments' => 'nullable|array',
            'payments.*.sri_payment_method_id' => 'required|integer|exists:pgsql.sri_payment_methods,id',
            'payments.*.total' => 'required|numeric|min:0',
            'payments.*.term' => 'nullable|integer|min:0',
            'additional_info' => 'nullable|array',
            'additional_info.*.name' => 'required|string|max:100',
            'additional_info.*.value' => 'nullable|string',
        ];
    }

    public static function toDTO(array $data): \App\Context\V1\Invoice\Application\DTOs\InvoiceDTO
    {
        return \App\Context\V1\Invoice\Application\DTOs\InvoiceDTO::fromArray($data);
    }
}
