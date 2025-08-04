<?php

namespace Eclipse\Catalogue\Models;

use Eclipse\Catalogue\Factories\PriceListFactory;
use Eclipse\World\Models\Currency;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceList extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pim_price_lists';

    protected $fillable = [
        'currency_id',
        'name',
        'code',
        'tax_included',
        'notes',
    ];

    protected $appends = [
        'is_active',
        'is_default',
        'is_default_purchase',
    ];

    /**
     * Get the currency for the price list
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the price list data for the price list
     */
    public function priceListData(): HasMany
    {
        return $this->hasMany(PriceListData::class);
    }

    /**
     * Get the tenant-specific data for the current tenant
     */
    public function currentTenantData(): ?PriceListData
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $tenantId = Filament::getTenant()?->id;

        if ($tenantFK && $tenantId) {
            return $this->priceListData()->where($tenantFK, $tenantId)->first();
        }

        return $this->priceListData()->first();
    }

    /**
     * Get tenant-specific data for a specific tenant
     *
     * @param  int|null  $tenantId  The tenant ID to get data for
     */
    public function getTenantData(?int $tenantId = null): ?PriceListData
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $targetTenantId = $tenantId ?: Filament::getTenant()?->id;

        if ($tenantFK && $targetTenantId) {
            return $this->priceListData()->where($tenantFK, $targetTenantId)->first();
        }

        return $this->priceListData()->first();
    }

    /**
     * Accessor for the is_active attribute
     */
    public function getIsActiveAttribute(): bool
    {
        if (isset($this->attributes['is_active'])) {
            return $this->attributes['is_active'];
        }

        $tenantData = $this->currentTenantData();

        return $tenantData ? $tenantData->is_active : true;
    }

    /**
     * Accessor for the is_default attribute
     */
    public function getIsDefaultAttribute(): bool
    {
        if (isset($this->attributes['is_default'])) {
            return $this->attributes['is_default'];
        }

        $tenantData = $this->currentTenantData();

        return $tenantData ? $tenantData->is_default : false;
    }

    /**
     * Accessor for the is_default_purchase attribute
     */
    public function getIsDefaultPurchaseAttribute(): bool
    {
        if (isset($this->attributes['is_default_purchase'])) {
            return $this->attributes['is_default_purchase'];
        }

        $tenantData = $this->currentTenantData();

        return $tenantData ? $tenantData->is_default_purchase : false;
    }

    /**
     * Get the default selling price list for the current tenant
     *
     * @param  int|null  $tenantId  The tenant ID to get the default for
     * @return static|null
     */
    public static function getDefaultSelling(?int $tenantId = null): ?self
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $currentTenantId = $tenantId ?: Filament::getTenant()?->id;

        $query = static::whereHas('priceListData', function ($q) use ($tenantFK, $currentTenantId) {
            $q->where('is_default', true);
            if ($tenantFK && $currentTenantId) {
                $q->where($tenantFK, $currentTenantId);
            }
        });

        return $query->first();
    }

    /**
     * Get the default purchase price list for the current tenant
     *
     * @param  int|null  $tenantId  The tenant ID to get the default for
     * @return static|null
     */
    public static function getDefaultPurchase(?int $tenantId = null): ?self
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $currentTenantId = $tenantId ?: Filament::getTenant()?->id;

        $query = static::whereHas('priceListData', function ($q) use ($tenantFK, $currentTenantId) {
            $q->where('is_default_purchase', true);
            if ($tenantFK && $currentTenantId) {
                $q->where($tenantFK, $currentTenantId);
            }
        });

        return $query->first();
    }

    protected function casts(): array
    {
        return [
            'tax_included' => 'boolean',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'is_default_purchase' => 'boolean',
        ];
    }

    /**
     * Create a new factory instance for the model
     */
    protected static function newFactory(): PriceListFactory
    {
        return PriceListFactory::new();
    }
}
