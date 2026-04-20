<?php

namespace App\Support\Knowledge;

final class MedicalDeviceDocumentType
{
    /** @return array<string, string> */
    public static function declarationLevelOptions(): array
    {
        return [
            'declaration_certificate' => __('ops.resources.medical_device_declaration_documents.types.declaration_certificate'),
            'owner_authorization' => __('ops.resources.medical_device_declaration_documents.types.owner_authorization'),
            'iso_certificate' => __('ops.resources.medical_device_declaration_documents.types.iso_certificate'),
            'circulation_registration' => __('ops.resources.medical_device_declaration_documents.types.circulation_registration'),
            'other_declaration' => __('ops.resources.medical_device_declaration_documents.types.other_declaration'),
        ];
    }

    /** @return array<string, string> */
    public static function skuLevelOptions(): array
    {
        return [
            'coa' => __('ops.resources.canonical_product_documents.types.coa'),
            'inspection_sheet' => __('ops.resources.canonical_product_documents.types.inspection_sheet'),
            'warranty_card' => __('ops.resources.canonical_product_documents.types.warranty_card'),
            'ifu' => __('ops.resources.canonical_product_documents.types.ifu'),
            'other_sku' => __('ops.resources.canonical_product_documents.types.other_sku'),
        ];
    }

    public static function isDeclarationLevel(?string $type): bool
    {
        return $type !== null && array_key_exists($type, self::declarationLevelOptions());
    }

    public static function isSkuLevel(?string $type): bool
    {
        return $type !== null && array_key_exists($type, self::skuLevelOptions());
    }
}
