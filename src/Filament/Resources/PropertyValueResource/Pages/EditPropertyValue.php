<?php

namespace Eclipse\Catalogue\Filament\Resources\PropertyValueResource\Pages;

use Eclipse\Catalogue\Filament\Resources\PropertyValueResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\LocaleSwitcher;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;

class EditPropertyValue extends EditRecord
{
    use Translatable;

    protected static string $resource = PropertyValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        $propertyId = request('property');
        if ($propertyId) {
            return PropertyValueResource::getUrl('index', ['property' => $propertyId]);
        }

        return PropertyValueResource::getUrl('index');
    }
}
