<?php

namespace Eclipse\Catalogue\Filament\Resources\ProductResource\Pages;

use Eclipse\Catalogue\Filament\Resources\Concerns\HandlesImageUploads;
use Eclipse\Catalogue\Filament\Resources\ProductResource;
use Eclipse\Catalogue\Models\Property;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;
    use HandlesImageUploads;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract property values from form data
        $propertyData = [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'property_values_')) {
                $propertyId = str_replace('property_values_', '', $key);
                $propertyData[$propertyId] = $value;
                unset($data[$key]);
            }
        }

        // Store property data for later use in afterCreate
        $this->propertyData = $propertyData;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Save property values
        if (isset($this->propertyData) && $this->record) {
            foreach ($this->propertyData as $propertyId => $values) {
                if ($values) {
                    $valuesToAttach = is_array($values) ? $values : [$values];
                    $valuesToAttach = array_filter($valuesToAttach); // Remove null values

                    if (! empty($valuesToAttach)) {
                        $this->record->propertyValues()->attach($valuesToAttach);
                    }
                }
            }
        }
    }
}
