<?php

namespace Eclipse\Catalogue\Filament\Resources\ProductResource\Pages;

use Eclipse\Catalogue\Filament\Resources\Concerns\HandlesImageUploads;
use Eclipse\Catalogue\Filament\Resources\ProductResource;
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
        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, 'property_values_')) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record) {
            $state = $this->form->getRawState();
            $propertyData = [];
            foreach ($state as $key => $value) {
                if (is_string($key) && str_starts_with($key, 'property_values_')) {
                    $propertyId = str_replace('property_values_', '', $key);
                    $propertyData[$propertyId] = $value;
                }
            }

            foreach ($propertyData as $propertyId => $values) {
                if ($values) {
                    $valuesToAttach = is_array($values) ? $values : [$values];
                    $valuesToAttach = array_filter($valuesToAttach);

                    if (! empty($valuesToAttach)) {
                        $this->record->propertyValues()->attach($valuesToAttach);
                    }
                }
            }
        }
    }
}
