<?php

namespace App\Support\Knowledge;

/**
 * Phân hạng rủi ro thiết bị y tế (A–D) cho hồ sơ chuẩn cấp sản phẩm.
 * Thiết lập nghiệp vụ: nhóm A, B — hồ sơ vĩnh viễn; nhóm C, D — hiệu lực đăng ký 5 năm.
 */
final class MedicalDeviceDossierClass
{
    public const A = 'A';

    public const B = 'B';

    public const C = 'C';

    public const D = 'D';

    /** @return list<string> */
    public static function all(): array
    {
        return [self::A, self::B, self::C, self::D];
    }

    /** @return array<string, string> value => label for Filament Select */
    public static function optionsForSelect(): array
    {
        return [
            self::A => __('ops.resources.canonical_product_documents.class_a'),
            self::B => __('ops.resources.canonical_product_documents.class_b'),
            self::C => __('ops.resources.canonical_product_documents.class_c'),
            self::D => __('ops.resources.canonical_product_documents.class_d'),
        ];
    }

    public static function isPermanent(?string $class): bool
    {
        return in_array($class, [self::A, self::B], true);
    }

    public static function hasFiveYearCycle(?string $class): bool
    {
        return in_array($class, [self::C, self::D], true);
    }

    public static function validityLabel(?string $class): string
    {
        if (self::isPermanent($class)) {
            return __('ops.resources.canonical_product_documents.validity_permanent');
        }
        if (self::hasFiveYearCycle($class)) {
            return __('ops.resources.canonical_product_documents.validity_five_years');
        }

        return '—';
    }
}
