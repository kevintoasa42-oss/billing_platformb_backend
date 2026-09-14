<?php

namespace App\Context\V3\Modules\Core\Product\Domain\Models;

class ProductSettings
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

    public static function fromArray(?array $settings): self
    {
        $settings ??= [];

        return new self(
            allowDuplicateNames: (bool) ($settings['allow_duplicate_names'] ?? false),
            requireBarcode: false,
            requireAuxiliaryCode: false,
            auxiliaryCodePrefix: trim((string) ($settings['auxiliary_code_prefix'] ?? '')),
            defaultProductType: in_array($settings['default_product_type'] ?? null, ['product', 'service'], true)
                ? $settings['default_product_type']
                : 'product',
            defaultIvaTypeId: isset($settings['default_iva_type_id']) ? (int) $settings['default_iva_type_id'] : null,
            requireDescription: (bool) ($settings['require_description'] ?? false),
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
