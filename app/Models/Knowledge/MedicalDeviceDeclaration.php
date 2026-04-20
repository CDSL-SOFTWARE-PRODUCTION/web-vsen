<?php

namespace App\Models\Knowledge;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalDeviceDeclaration extends Model
{
    protected $fillable = [
        'declaration_number',
        'declared_on',
        'issuer',
        'device_risk_class',
        'device_name_official',
        'declaring_organization',
        'declaring_address',
        'internal_reference_code',
        'internal_reference_date',
        'quality_standard',
        'legal_owner_name',
        'legal_owner_address',
        'warranty',
        'notes',
        'extra',
    ];

    protected function casts(): array
    {
        return [
            'declared_on' => 'date',
            'internal_reference_date' => 'date',
            'warranty' => 'array',
            'extra' => 'array',
        ];
    }

    public function canonicalProducts(): HasMany
    {
        return $this->hasMany(CanonicalProduct::class);
    }

    public function productFamilies(): HasMany
    {
        return $this->hasMany(ProductFamily::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(MedicalDeviceDeclarationDocument::class);
    }
}
