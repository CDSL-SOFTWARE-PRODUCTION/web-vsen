<?php

namespace App\Models\Knowledge;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalDeviceDeclarationDocument extends Model
{
    protected $fillable = [
        'medical_device_declaration_id',
        'document_type',
        'status',
        'file_path',
        'expiry_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'medical_device_declaration_id' => 'integer',
            'expiry_date' => 'date',
        ];
    }

    public function medicalDeviceDeclaration(): BelongsTo
    {
        return $this->belongsTo(MedicalDeviceDeclaration::class);
    }
}
