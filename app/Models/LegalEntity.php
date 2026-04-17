<?php

namespace App\Models;

use App\Models\Demand\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalEntity extends Model
{
    protected $fillable = [
        'name',
        'tax_code',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
