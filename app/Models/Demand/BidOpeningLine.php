<?php

namespace App\Models\Demand;

use App\Models\Knowledge\CanonicalProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BidOpeningLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'bid_opening_session_id',
        'source_row_no',
        'lot_code',
        'item_name',
        'canonical_product_id',
        'mapping_status',
        'mapping_note',
        'mapped_at',
        'bidder_identifier',
        'bidder_name',
        'bid_valid_days',
        'bid_security_value',
        'bid_security_days',
        'bid_price',
        'discount_rate',
        'bid_price_after_discount',
        'delivery_commitment',
        'currency',
        'row_fingerprint',
    ];

    protected function casts(): array
    {
        return [
            'bid_opening_session_id' => 'integer',
            'source_row_no' => 'integer',
            'canonical_product_id' => 'integer',
            'bid_valid_days' => 'integer',
            'bid_security_days' => 'integer',
            'bid_security_value' => 'decimal:2',
            'bid_price' => 'decimal:2',
            'discount_rate' => 'decimal:4',
            'bid_price_after_discount' => 'decimal:2',
            'mapped_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (BidOpeningLine $line): void {
            if (is_string($line->row_fingerprint) && $line->row_fingerprint !== '') {
                return;
            }

            $line->row_fingerprint = hash('sha256', implode('|', [
                (string) ($line->bid_opening_session_id ?? ''),
                (string) ($line->lot_code ?? ''),
                (string) ($line->bidder_identifier ?? ''),
                (string) ($line->bidder_name ?? ''),
                (string) ($line->bid_price ?? ''),
            ]));
        });
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(BidOpeningSession::class, 'bid_opening_session_id');
    }

    public function canonicalProduct(): BelongsTo
    {
        return $this->belongsTo(CanonicalProduct::class);
    }

    public function isMapped(): bool
    {
        return $this->canonical_product_id !== null && $this->mapping_status === 'mapped';
    }
}
