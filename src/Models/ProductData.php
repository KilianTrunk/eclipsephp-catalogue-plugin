<?php

namespace Eclipse\Catalogue\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductData extends Model
{
    protected $table = 'catalogue_product_data';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'sorting_label',
        'is_active',
        'available_from_date',
        'has_free_delivery',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'available_from_date' => 'datetime',
            'has_free_delivery' => 'boolean',
        ];
    }
}
