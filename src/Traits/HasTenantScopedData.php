<?php

namespace Eclipse\Catalogue\Traits;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasTenantScopedData
{
    /**
     * Configuration for tenant-scoped behavior.
     * Override these properties in your model to customize behavior.
     *
     * Required properties that must be defined in the model:
     * - $tenantDataRelation: The name of the relationship method
     * - $tenantDataModel: The class name of the tenant data model
     * - $tenantFlags: Array of flag names to handle
     * - $mutuallyExclusiveFlagSets: Array of arrays containing mutually exclusive flags
     * - $uniqueFlagsPerTenant: Array of flags that should be unique per tenant
     */

    /**
     * Get all per-tenant data rows for this model.
     */
    public function getTenantDataRelation(): HasMany
    {
        $relationName = $this->getTenantDataRelationName();

        return $this->$relationName();
    }

    /**
     * Get the tenant data relation name from the model.
     */
    protected function getTenantDataRelationName(): string
    {
        $reflection = new \ReflectionClass(static::class);
        $property = $reflection->getProperty('tenantDataRelation');
        $property->setAccessible(true);

        return $property->getValue();
    }

    /**
     * Get the tenant data model class from the model.
     */
    protected function getTenantDataModelClass(): string
    {
        $reflection = new \ReflectionClass(static::class);
        $property = $reflection->getProperty('tenantDataModel');
        $property->setAccessible(true);

        return $property->getValue();
    }

    /**
     * Get the tenant flags from the model.
     */
    protected function getTenantFlags(): array
    {
        $reflection = new \ReflectionClass(static::class);
        $property = $reflection->getProperty('tenantFlags');
        $property->setAccessible(true);

        return $property->getValue() ?? [];
    }

    /**
     * Get the mutually exclusive flag sets from the model.
     */
    protected function getMutuallyExclusiveFlagSets(): array
    {
        $reflection = new \ReflectionClass(static::class);
        $property = $reflection->getProperty('mutuallyExclusiveFlagSets');
        $property->setAccessible(true);

        return $property->getValue() ?? [];
    }

    /**
     * Get the mutually exclusive flag sets from the model (static version).
     */
    protected static function getStaticMutuallyExclusiveFlagSets(): array
    {
        $reflection = new \ReflectionClass(static::class);
        $property = $reflection->getProperty('mutuallyExclusiveFlagSets');
        $property->setAccessible(true);

        return $property->getValue() ?? [];
    }

    /**
     * Get the unique flags per tenant from the model.
     */
    protected function getUniqueFlagsPerTenant(): array
    {
        $reflection = new \ReflectionClass(static::class);
        $property = $reflection->getProperty('uniqueFlagsPerTenant');
        $property->setAccessible(true);

        return $property->getValue() ?? [];
    }

    /**
     * Get additional per-tenant attributes (non-boolean flags) from the model.
     */
    protected function getTenantAttributes(): array
    {
        $reflection = new \ReflectionClass(static::class);
        if ($reflection->hasProperty('tenantAttributes')) {
            $property = $reflection->getProperty('tenantAttributes');
            $property->setAccessible(true);

            return $property->getValue() ?? [];
        }

        return [];
    }

    /**
     * Get the per-tenant data for the current Filament tenant (if any).
     * Falls back to the first data row when tenancy is disabled.
     */
    public function currentTenantData()
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $tenantId = Filament::getTenant()?->id;

        if ($tenantFK && $tenantId) {
            return $this->getTenantDataRelation()->where($tenantFK, $tenantId)->first();
        }

        return $this->getTenantDataRelation()->first();
    }

    /**
     * Get per-tenant data for a specific tenant ID.
     * If no tenantId provided, the current Filament tenant is used.
     */
    public function getTenantData(?int $tenantId = null)
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $targetTenantId = $tenantId ?: Filament::getTenant()?->id;

        if ($tenantFK && $targetTenantId) {
            return $this->getTenantDataRelation()->where($tenantFK, $targetTenantId)->first();
        }

        return $this->getTenantDataRelation()->first();
    }

    /**
     * Create dynamic accessors for tenant flags.
     */
    public function getAttribute($key)
    {
        // Check if this is one of our tenant flags
        if (in_array($key, $this->getTenantFlags())) {
            return $this->getTenantFlagValue($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * Get the value of a tenant flag from current tenant data.
     */
    protected function getTenantFlagValue(string $flag)
    {
        if (isset($this->attributes[$flag])) {
            return $this->attributes[$flag];
        }

        $tenantData = $this->currentTenantData();
        $default = in_array($flag, ['is_active']) ? true : false;

        return $tenantData ? $tenantData->$flag : $default;
    }

    /**
     * Create a new model together with its per-tenant settings.
     */
    public static function createWithTenantData(array $mainData, array $tenantData = []): self
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        // Create the main record
        $model = static::create($mainData);

        if (! $tenantFK) {
            // No tenancy: create a single data row with provided or default flags
            $tenantFlags = $model->getTenantFlags();
            $tenantAttrs = $model->getTenantAttributes();
            $allowedKeys = array_merge($tenantFlags, $tenantAttrs);
            $singleTenantData = $tenantData ?: array_fill_keys($tenantFlags, true);

            // Set defaults for specific flags
            foreach ($tenantFlags as $flag) {
                if (! isset($singleTenantData[$flag])) {
                    $singleTenantData[$flag] = in_array($flag, ['is_active']) ? true : false;
                }
            }

            // Filter to allowed keys only
            $singleTenantData = array_intersect_key($singleTenantData, array_flip($allowedKeys));

            // Enforce invariants
            $model->handleDefaultConstraints($singleTenantData, null);

            $dataModelClass = $model->getTenantDataModelClass();
            $relationKey = $model->getTenantDataRelation()->getForeignKeyName();

            $dataModelClass::create([
                $relationKey => $model->id,
                ...$singleTenantData,
            ]);

            return $model;
        }

        // Tenancy enabled: create a data row for each tenant
        $tenantModel = config('eclipse-catalogue.tenancy.model');
        $tenants = $tenantModel::all();

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;

            // Use provided data if available; otherwise apply safe defaults
            $tenantFlags = $model->getTenantFlags();
            $tenantAttrs = $model->getTenantAttributes();
            $allowedKeys = array_merge($tenantFlags, $tenantAttrs);
            $tenantSpecificData = $tenantData[$tenantId] ?? array_fill_keys($tenantFlags, false);

            // Set defaults for specific flags
            foreach ($tenantFlags as $flag) {
                if (! isset($tenantSpecificData[$flag])) {
                    $tenantSpecificData[$flag] = in_array($flag, ['is_active']) ? true : false;
                }
            }

            // Filter to allowed keys only
            $tenantSpecificData = array_intersect_key($tenantSpecificData, array_flip($allowedKeys));

            // Enforce invariants for this tenant
            $model->handleDefaultConstraints($tenantSpecificData, $tenantId);

            $dataModelClass = $model->getTenantDataModelClass();
            $relationKey = $model->getTenantDataRelation()->getForeignKeyName();

            $dataModelClass::create([
                $relationKey => $model->id,
                $tenantFK => $tenantId,
                ...$tenantSpecificData,
            ]);
        }

        return $model;
    }

    /**
     * Update the base model row and its per-tenant settings.
     */
    public function updateWithTenantData(array $mainData = [], array $tenantData = []): self
    {
        // Update main record if data provided
        if (! empty($mainData)) {
            $this->update($mainData);
        }

        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy: upsert a single data row
            if (! empty($tenantData)) {
                $this->handleDefaultConstraints($tenantData, null);

                $dataModelClass = static::$tenantDataModel;
                $relationKey = $this->getTenantDataRelation()->getForeignKeyName();

                $dataModelClass::updateOrCreate(
                    [$relationKey => $this->id],
                    $tenantData
                );
            }

            return $this;
        }

        // Tenancy enabled: update/create records for all tenants
        $tenantModel = config('eclipse-catalogue.tenancy.model');
        $tenants = $tenantModel::all();

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;
            $tenantFlags = $this->getTenantFlags();
            $tenantSpecificData = $tenantData[$tenantId] ?? array_fill_keys($tenantFlags, false);

            // Set defaults for specific flags
            foreach ($tenantFlags as $flag) {
                if (! isset($tenantSpecificData[$flag])) {
                    $tenantSpecificData[$flag] = in_array($flag, ['is_active']) ? true : false;
                }
            }

            $this->handleDefaultConstraints($tenantSpecificData, $tenantId);

            $dataModelClass = $this->getTenantDataModelClass();
            $relationKey = $this->getTenantDataRelation()->getForeignKeyName();

            $dataModelClass::updateOrCreate(
                [
                    $relationKey => $this->id,
                    $tenantFK => $tenantId,
                ],
                $tenantSpecificData
            );
        }

        return $this;
    }

    /**
     * Enforce constraints for unique flags and mutually exclusive flag sets.
     */
    public function handleDefaultConstraints(array &$tenantData, ?int $tenantId): void
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        // Check mutually exclusive flag sets
        foreach ($this->getMutuallyExclusiveFlagSets() as $exclusiveSet) {
            $activeFlags = array_filter($exclusiveSet, fn ($flag) => $tenantData[$flag] ?? false);

            if (count($activeFlags) > 1) {
                $errorKey = $tenantId ? "tenant_data.{$tenantId}" : '';
                $errors = [];
                foreach ($activeFlags as $flag) {
                    $errors[$errorKey ? "{$errorKey}.{$flag}" : $flag] = 'These options cannot be enabled simultaneously.';
                }
                throw \Illuminate\Validation\ValidationException::withMessages($errors);
            }
        }

        // Handle unique flags per tenant
        foreach ($this->getUniqueFlagsPerTenant() as $uniqueFlag) {
            if ($tenantData[$uniqueFlag] ?? false) {
                $dataModelClass = $this->getTenantDataModelClass();
                $relationKey = $this->getTenantDataRelation()->getForeignKeyName();

                $query = $dataModelClass::where($uniqueFlag, true);

                // Exclude current record if it exists (for updates)
                if ($this->exists) {
                    $query->where($relationKey, '!=', $this->id);
                }

                if ($tenantFK && $tenantId) {
                    $query->where($tenantFK, $tenantId);
                }

                $query->update([$uniqueFlag => false]);
            }
        }
    }

    /**
     * Validate tenant data constraints before saving.
     */
    public static function validateTenantDataConstraints(array $tenantData): void
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy - validate simple fields
            foreach (static::getStaticMutuallyExclusiveFlagSets() as $exclusiveSet) {
                $activeFlags = array_filter($exclusiveSet, fn ($flag) => $tenantData[$flag] ?? false);

                if (count($activeFlags) > 1) {
                    $errors = [];
                    foreach ($activeFlags as $flag) {
                        $errors[$flag] = 'These options cannot be enabled simultaneously.';
                    }
                    throw \Illuminate\Validation\ValidationException::withMessages($errors);
                }
            }

            return;
        }

        // Validate tenant data
        $errors = [];
        $firstErrorTenantId = null;

        foreach ($tenantData as $tenantId => $tenantSpecificData) {
            foreach (static::getStaticMutuallyExclusiveFlagSets() as $exclusiveSet) {
                $activeFlags = array_filter($exclusiveSet, fn ($flag) => $tenantSpecificData[$flag] ?? false);

                if (count($activeFlags) > 1) {
                    $tenantModel = config('eclipse-catalogue.tenancy.model');
                    $tenant = $tenantModel::find($tenantId);
                    $tenantName = $tenant ? $tenant->name : "Tenant {$tenantId}";

                    if (! $firstErrorTenantId) {
                        $firstErrorTenantId = $tenantId;
                    }

                    foreach ($activeFlags as $flag) {
                        $errors["tenant_data.{$tenantId}.{$flag}"] = "These options cannot be enabled simultaneously for {$tenantName}.";
                    }
                }
            }
        }

        if (! empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }
}
