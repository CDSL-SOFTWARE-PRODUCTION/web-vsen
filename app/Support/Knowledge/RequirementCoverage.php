<?php

namespace App\Support\Knowledge;

use App\Models\Knowledge\Requirement;

final class RequirementCoverage
{
    /**
     * @return array<string, list<string>>
     */
    public static function skuDocumentTypeMap(): array
    {
        return [
            'Catalog' => ['ifu'],
            'FSC' => ['coa'],
            'ISO_13485' => [],
            'CE' => [],
        ];
    }

    /**
     * @return list<string>
     */
    public static function expectedSkuDocumentTypes(Requirement $requirement): array
    {
        $map = self::skuDocumentTypeMap();

        return $map[$requirement->type] ?? [];
    }
}
