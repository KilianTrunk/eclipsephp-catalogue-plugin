<?php

namespace Eclipse\Catalogue\Filament\Resources\PropertyValueResource\Pages;

use Eclipse\Catalogue\Filament\Resources\PropertyValueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPropertyValue extends EditRecord
{
    protected static string $resource = PropertyValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        if (request()->has('property')) {
            return PropertyValueResource::getUrl('index', ['property' => request('property')]);
        }

        return parent::getRedirectUrl();
    }
}
