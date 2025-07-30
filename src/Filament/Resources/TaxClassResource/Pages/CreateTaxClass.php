<?php

namespace Eclipse\Catalogue\Filament\Resources\TaxClassResource\Pages;

use Eclipse\Catalogue\Filament\Resources\TaxClassResource;
use Eclipse\Catalogue\Models\TaxClass;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxClass extends CreateRecord
{
    protected static string $resource = TaxClassResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If this class is being set as default, unset all other defaults
        if ($data['is_default'] ?? false) {
            TaxClass::where('is_default', true)->update(['is_default' => false]);
        }

        // Auto-set current tenant_id if tenancy is configured
        if (config('eclipse-catalogue.tenancy.foreign_key')) {
            $currentTenant = Filament::getTenant();
            if ($currentTenant) {
                $data[config('eclipse-catalogue.tenancy.foreign_key')] = $currentTenant->id;
            } else {
                throw new \Exception('Current tenant not available. Cannot create TaxClass without tenant context.');
            }
        }

        return $data;
    }
}
