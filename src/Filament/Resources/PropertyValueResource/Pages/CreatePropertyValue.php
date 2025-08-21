<?php

namespace Eclipse\Catalogue\Filament\Resources\PropertyValueResource\Pages;

use Eclipse\Catalogue\Filament\Resources\PropertyValueResource;
use Eclipse\Catalogue\Models\Property;
use Filament\Resources\Pages\CreateRecord;

class CreatePropertyValue extends CreateRecord
{
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

    protected function getRedirectUrl(): string
    {
        if (request()->has('property')) {
            return PropertyValueResource::getUrl('index', ['property' => request('property')]);
        }

        return parent::getRedirectUrl();
    }
}
