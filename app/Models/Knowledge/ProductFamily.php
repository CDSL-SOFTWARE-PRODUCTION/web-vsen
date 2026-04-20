<?php

namespace App\Models\Knowledge;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductFamily extends Model
{
    protected $fillable = [
        'name',
        'medical_device_declaration_id',
        'description',
    ];

    public function medicalDeviceDeclaration(): BelongsTo
    {
        return $this->belongsTo(MedicalDeviceDeclaration::class);
    }

    public function canonicalProducts(): HasMany
    {
        return $this->hasMany(CanonicalProduct::class);
    }
}
