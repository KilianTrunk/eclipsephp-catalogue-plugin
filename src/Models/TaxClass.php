<?php

namespace Eclipse\Catalogue\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class TaxClass extends Model
{
    use SoftDeletes;

    protected $table = 'pim_tax_classes';

    public function getFillable(): array
    {
        $fillable = [
            'name',
            'description',
            'rate',
            'is_default',
        ];

        if (config('eclipse-catalogue.tenancy.foreign_key')) {
            $fillable[] = config('eclipse-catalogue.tenancy.foreign_key');
        }

        return $fillable;
    }

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_default' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // If this class is being set as default, unset all other defaults
            if ($model->is_default) {
                static::where('is_default', true)
                    ->where('id', '!=', $model->id)
                    ->update(['is_default' => false]);
            }
        });

        static::deleting(function ($model) {
            // Prevent deletion of default class
            if ($model->is_default) {
                throw ValidationException::withMessages([
                    'is_default' => 'Cannot delete the default tax class.',
                ]);
            }
        });
    }

    /**
     * Get the default tax class
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Check if this is the default class
     */
    public function isDefault(): bool
    {
        return $this->is_default;
    }

    /** @return BelongsTo<\Eclipse\Core\Models\Site, self> */
    public function site(): BelongsTo
    {
        return $this->belongsTo(\Eclipse\Core\Models\Site::class);
    }
}
