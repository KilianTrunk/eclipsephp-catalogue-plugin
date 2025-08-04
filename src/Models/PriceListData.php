<?php

namespace Eclipse\Catalogue\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceListData extends Model
{
    use HasFactory;

    protected $table = 'pim_price_list_data';

    public $timestamps = false;

    protected $fillable = [
        'price_list_id',
        'is_active',
        'is_default',
        'is_default_purchase',
    ];

    public function getFillable(): array
    {
        $fillable = $this->fillable;

        if (config('eclipse-catalogue.tenancy.foreign_key')) {
            $fillable[] = config('eclipse-catalogue.tenancy.foreign_key');
        }

        return $fillable;
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    /** @return BelongsTo<\Eclipse\Core\Models\Site, self> */
    public function site(): BelongsTo
    {
        return $this->belongsTo(\Eclipse\Core\Models\Site::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'is_default_purchase' => 'boolean',
        ];
    }

    protected static function newFactory()
    {
        return \Eclipse\Catalogue\Factories\PriceListDataFactory::new();
    }
}
