<?php

namespace App\Models\Knowledge;

use Illuminate\Database\Eloquent\Model;

class CanonicalProduct extends Model
{
    protected $fillable = [
        'sku',
        'raw_name',
        'abc_class',
        'spec_json',
    ];

    protected function casts(): array
    {
        return [
            'spec_json' => 'array',
        ];
    }
}
