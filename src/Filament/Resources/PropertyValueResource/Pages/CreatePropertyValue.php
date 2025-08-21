<?php

namespace Eclipse\Catalogue\Filament\Resources\PropertyValueResource\Pages;

use Eclipse\Catalogue\Filament\Resources\PropertyValueResource;
use Eclipse\Catalogue\Models\Property;
use Filament\Actions\LocaleSwitcher;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreatePropertyValue extends CreateRecord
{
    use Translatable;

    protected static string $resource = PropertyValueResource::class;

    public function mount(): void
    {
        parent::mount();

        if (request()->has('property')) {
            $property = Property::find(request('property'));
            if ($property) {
                $this->form->fill(['property_id' => $property->id]);
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
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
