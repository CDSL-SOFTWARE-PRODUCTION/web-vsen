<?php

namespace App\Models\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CmsProduct extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'sku',
        'slug',
        'name',
        'short_description',
        'description',
        'category_id',
        'price',
        'show_price',
        'status',
        'brand',
        'model',
        'video_url',
        'meta_title',
        'meta_description',
        'is_featured',
        'is_active',
        'view_count',
        'images',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'show_price' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'view_count' => 'integer',
        'images' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        if (!empty($this->images) && is_array($this->images)) {
            return $this->images[0];
        }

        return null;
    }

    public function specs(): HasMany
    {
        return $this->hasMany(ProductSpec::class, 'product_id')->orderBy('sort_order');
    }

    public function quoteRequests(): HasMany
    {
        return $this->hasMany(QuoteRequest::class, 'product_id');
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(CmsProduct::class, 'product_relations', 'product_id', 'related_product_id');
    }
}
