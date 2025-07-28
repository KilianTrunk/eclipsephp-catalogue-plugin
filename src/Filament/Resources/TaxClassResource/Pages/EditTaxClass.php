<?php

namespace Eclipse\Catalogue\Filament\Resources\TaxClassResource\Pages;

use Eclipse\Catalogue\Filament\Resources\TaxClassResource;
use Eclipse\Catalogue\Models\TaxClass;
use Eclipse\Core\Services\Registry;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
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

        // Ensure site_id is preserved if tenancy is configured
        if (config('eclipse-catalogue.tenancy.foreign_key') && ! isset($data[config('eclipse-catalogue.tenancy.foreign_key')])) {
            // Use current site from Registry or preserve existing site_id
            $currentSite = Registry::getSite();
            if ($currentSite) {
                $data[config('eclipse-catalogue.tenancy.foreign_key')] = $currentSite->id;
            } else {
                $data[config('eclipse-catalogue.tenancy.foreign_key')] = $this->record->getAttribute(config('eclipse-catalogue.tenancy.foreign_key'));
            }
        }

        return $data;
    }
}
