<?php

namespace Eclipse\Catalogue\Filament\Resources\PropertyValueResource\Pages;

use Eclipse\Catalogue\Filament\Resources\PropertyValueResource;
use Eclipse\Catalogue\Models\Property;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPropertyValues extends ListRecords
{
    protected static string $resource = PropertyValueResource::class;

    public ?Property $property = null;

    public function mount(): void
    {
        parent::mount();

        if (request()->has('property')) {
            $this->property = Property::find(request('property'));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->url(fn (): string => PropertyValueResource::getUrl('create', ['property' => $this->property?->id])),
        ];
    }

    public function getTitle(): string
    {
        if ($this->property) {
            return "Values for: {$this->property->name}";
        }

        return 'Property Values';
    }
}
