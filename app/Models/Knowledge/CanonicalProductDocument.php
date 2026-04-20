<?php

namespace App\Models\Knowledge;

use App\Support\Knowledge\MedicalDeviceDocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class CanonicalProductDocument extends Model
{
    protected $fillable = [
        'canonical_product_id',
        'document_type',
        'document_group',
        'status',
        'file_path',
        'expiry_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'canonical_product_id' => 'integer',
            'expiry_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (CanonicalProductDocument $document): void {
            if ($document->document_type === null || $document->document_type === '') {
                return;
            }
            if (MedicalDeviceDocumentType::isDeclarationLevel($document->document_type)) {
                throw new RuntimeException('Declaration-level document type is not allowed on SKU documents.');
            }

            if ($document->document_group === null || $document->document_group === '') {
                $class = CanonicalProduct::query()
                    ->whereKey($document->canonical_product_id)
                    ->with('medicalDeviceDeclaration:id,device_risk_class')
                    ->first()
                    ?->medicalDeviceDeclaration
                    ?->device_risk_class;
                $document->document_group = $class;
            }
        });
    }

    public function canonicalProduct(): BelongsTo
    {
        return $this->belongsTo(CanonicalProduct::class);
    }
}
