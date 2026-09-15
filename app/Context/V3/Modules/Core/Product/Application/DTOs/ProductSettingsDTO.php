<?php

namespace App\Context\V3\Modules\Core\Product\Application\DTOs;

class ProductSettingsDTO
{
    public function __construct(
        public readonly bool $allowDuplicateNames = false,
        public readonly bool $requireBarcode = false,
        public readonly bool $requireAuxiliaryCode = false,
        public readonly string $auxiliaryCodePrefix = '',
        public readonly string $defaultProductType = 'product',
        public readonly ?int $defaultIvaTypeId = null,
        public readonly bool $requireDescription = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            allowDuplicateNames: (bool) ($data['allow_duplicate_names'] ?? false),
            requireBarcode: (bool) ($data['require_barcode'] ?? false),
            requireAuxiliaryCode: (bool) ($data['require_auxiliary_code'] ?? false),
            auxiliaryCodePrefix: trim((string) ($data['auxiliary_code_prefix'] ?? '')),
            defaultProductType: in_array($data['default_product_type'] ?? null, ['product', 'service'], true)
                ? $data['default_product_type']
                : 'product',
            defaultIvaTypeId: isset($data['default_iva_type_id']) ? (int) $data['default_iva_type_id'] : null,
            requireDescription: (bool) ($data['require_description'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'allow_duplicate_names' => $this->allowDuplicateNames,
            'require_barcode' => false,
            'require_auxiliary_code' => false,
            'auxiliary_code_prefix' => $this->auxiliaryCodePrefix,
            'default_product_type' => $this->defaultProductType,
            'default_iva_type_id' => $this->defaultIvaTypeId,
            'require_description' => $this->requireDescription,
        ];
    }
}
