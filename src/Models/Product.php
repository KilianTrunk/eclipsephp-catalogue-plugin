<?php

namespace Eclipse\Catalogue\Models;

use Eclipse\Catalogue\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'catalogue_products';

    protected $fillable = [
        'code',
        'barcode',
        'manufacturers_code',
        'suppliers_code',
        'net_weight',
        'gross_weight',
        'name',
        'short_description',
        'description',
    ];

    public array $translatable = [
        'name',
        'short_description',
        'description',
    ];

    protected $casts = [
        'name' => 'array',
        'short_description' => 'array',
        'description' => 'array',
    ];

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
