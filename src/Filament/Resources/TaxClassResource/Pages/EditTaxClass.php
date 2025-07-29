<?php

namespace Eclipse\Catalogue\Filament\Resources\TaxClassResource\Pages;

use Eclipse\Catalogue\Filament\Resources\TaxClassResource;
use Eclipse\Catalogue\Models\TaxClass;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditTaxClass extends EditRecord
{
    protected static string $resource = TaxClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If this class is being set as default, unset all other defaults
        if ($data['is_default'] ?? false) {
            TaxClass::where('is_default', true)
                ->where('id', '!=', $this->record->id)
                ->update(['is_default' => false]);
        }

        // Ensure tenant id is preserved if tenancy is configured
        if (config('eclipse-catalogue.tenancy.foreign_key') && ! isset($data[config('eclipse-catalogue.tenancy.foreign_key')])) {
            // Use current tenant from Filament or preserve existing
            $currentTenant = Filament::getTenant();
            if ($currentTenant) {
                $data[config('eclipse-catalogue.tenancy.foreign_key')] = $currentTenant->id;
            } else {
                $data[config('eclipse-catalogue.tenancy.foreign_key')] = $this->record->getAttribute(config('eclipse-catalogue.tenancy.foreign_key'));
            }
        }

        return $data;
    }
}
