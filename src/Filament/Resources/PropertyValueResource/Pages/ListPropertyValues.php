<?php

namespace Eclipse\Catalogue\Filament\Resources\PropertyValueResource\Pages;

use Eclipse\Catalogue\Filament\Resources\PropertyResource;
use Eclipse\Catalogue\Filament\Resources\PropertyValueResource;
use Eclipse\Catalogue\Models\Property;
use Filament\Actions;
use Filament\Actions\LocaleSwitcher;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;

class ListPropertyValues extends ListRecords
{
    use Translatable;

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
            LocaleSwitcher::make(),
            Actions\CreateAction::make()
                ->url(fn (): string => PropertyValueResource::getUrl('create', ['property' => $this->property?->id])),
        ];
    }

    public function getTitle(): string
    {
        return $this->property
            ? __('eclipse-catalogue::property-value.pages.title.with_property', ['property' => $this->property->name])
            : __('eclipse-catalogue::property-value.pages.title.default');
    }

    public function getBreadcrumbs(): array
    {
        if ($this->property) {
            return [
                PropertyResource::getUrl('index') => __('eclipse-catalogue::property-value.pages.breadcrumbs.properties'),
                null => $this->property->name,
                request()->url() => __('eclipse-catalogue::property-value.pages.breadcrumbs.list'),
            ];
        }

        return [
            PropertyValueResource::getUrl('index') => __('eclipse-catalogue::property-value.pages.title.default'),
            request()->url() => __('eclipse-catalogue::property-value.pages.breadcrumbs.list'),
        ];
    }
}
