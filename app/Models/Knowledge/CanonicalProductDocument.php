<?php

namespace App\Models\Knowledge;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function canonicalProduct(): BelongsTo
    {
        return $this->belongsTo(CanonicalProduct::class);
    }
}
