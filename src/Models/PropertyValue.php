<?php

namespace Eclipse\Catalogue\Models;

use Eclipse\Catalogue\Factories\PropertyValueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class PropertyValue extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $table = 'pim_property_value';

    protected $fillable = [
        'property_id',
        'value',
        'sort',
        'info_url',
        'image',
    ];

    public array $translatable = [
        'value',
        'info_url',
        'image',
    ];

    protected $casts = [
        'sort' => 'integer',
        'property_id' => 'integer',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'catalogue_product_has_property_value', 'property_value_id', 'product_id')
            ->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])
            ->useDisk('public');
    }

    protected static function booted(): void
    {
        static::deleting(function (PropertyValue $value) {
            if ($value->isForceDeleting()) {
                // Delete product assignments
                $value->products()->detach();
            }
        });
    }

    protected static function newFactory(): PropertyValueFactory
    {
        return PropertyValueFactory::new();
    }

    /**
     * Ensure Filament receives scalar values for form hydration.
     *
     * In particular, return the current-locale string (or null) for the
     * translatable `image` attribute instead of the full translations array.
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        if (array_key_exists('image', $attributes) && is_array($attributes['image'])) {
            $translation = $this->getTranslation('image', app()->getLocale());
            $attributes['image'] = $translation !== '' ? $translation : null;
        }

        return $attributes;
    }
}
