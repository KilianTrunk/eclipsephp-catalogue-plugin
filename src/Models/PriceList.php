<?php

namespace Eclipse\Catalogue\Models;

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

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function priceListData(): HasMany
    {
        return $this->hasMany(PriceListData::class);
    }

    /**
     * Get the tenant-specific data for the current tenant
     */
    public function currentTenantData()
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
     */
    public function getTenantData(?int $tenantId = null)
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $targetTenantId = $tenantId ?: Filament::getTenant()?->id;

        if ($tenantFK && $targetTenantId) {
            return $this->priceListData()->where($tenantFK, $targetTenantId)->first();
        }

        return $this->priceListData()->first();
    }

    // Removed problematic global scope - using accessor methods instead

    // Accessor methods for tenant-scoped attributes
    public function getIsActiveAttribute()
    {
        if (isset($this->attributes['is_active'])) {
            return $this->attributes['is_active'];
        }

        $tenantData = $this->currentTenantData();

        return $tenantData ? $tenantData->is_active : true;
    }

    public function getIsDefaultAttribute()
    {
        if (isset($this->attributes['is_default'])) {
            return $this->attributes['is_default'];
        }

        $tenantData = $this->currentTenantData();

        return $tenantData ? $tenantData->is_default : false;
    }

    public function getIsDefaultPurchaseAttribute()
    {
        if (isset($this->attributes['is_default_purchase'])) {
            return $this->attributes['is_default_purchase'];
        }

        $tenantData = $this->currentTenantData();

        return $tenantData ? $tenantData->is_default_purchase : false;
    }

    /**
     * Get the default selling price list for current tenant
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
     * Get the default purchase price list for current tenant
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

    protected static function newFactory()
    {
        return \Eclipse\Catalogue\Factories\PriceListFactory::new();
    }
}
