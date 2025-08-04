<?php

namespace Eclipse\Catalogue\Livewire;

use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Livewire\Component as LivewireComponent;

class TenantSwitcher extends LivewireComponent
{
    public $selectedTenant;

    public $tenants;

    public $currentTenant;

    public $fieldName;

    public $label;

    public $placeholder;

    public function mount($fieldName = 'selected_tenant', $label = null, $placeholder = null)
    {
        $this->fieldName = $fieldName;
        $this->label = $label ?? __('eclipse-catalogue::price-list.fields.tenant');
        $this->placeholder = $placeholder ?? __('eclipse-catalogue::price-list.placeholders.tenant');

        $tenantModel = config('eclipse-catalogue.tenancy.model');
        $this->tenants = $tenantModel::all();
        $this->currentTenant = Filament::getTenant();

        // Set initial value to current tenant
        $this->selectedTenant = $this->currentTenant?->id;
    }

    public function updatedSelectedTenant($value)
    {
        $this->dispatch('tenant-changed', $value);
    }

    public function render()
    {
        return view('eclipse-catalogue::livewire.tenant-switcher');
    }

    /**
     * Create a Filament form component for tenant switching
     */
    public static function make(string $fieldName = 'selected_tenant'): Component
    {
        $tenantModel = config('eclipse-catalogue.tenancy.model');

        if (! $tenantModel) {
            return Select::make($fieldName)->hidden();
        }

        $tenants = $tenantModel::all();
        $currentTenant = Filament::getTenant();

        return Select::make($fieldName)
            ->label(__('eclipse-catalogue::price-list.fields.tenant'))
            ->placeholder(__('eclipse-catalogue::price-list.placeholders.tenant'))
            ->options($tenants->pluck('name', 'id')->map(function ($name, $id) use ($currentTenant) {
                return $id === $currentTenant?->id
                    ? $name.' ('.__('eclipse-catalogue::price-list.labels.current').')'
                    : $name;
            }))
            ->default($currentTenant?->id)
            ->selectablePlaceholder(false)
            ->live()
            ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                // Trigger form update when tenant changes
                $livewire->dispatch('tenant-changed', $state);
            })
            ->required();
    }

    /**
     * Create a tenant switcher with custom options
     */
    public static function makeWithOptions(
        string $fieldName = 'selected_tenant',
        array $options = [],
        ?string $label = null,
        ?string $placeholder = null
    ): Component {
        $tenantModel = config('eclipse-catalogue.tenancy.model');

        if (! $tenantModel) {
            return Select::make($fieldName)->hidden();
        }

        $tenants = $tenantModel::all();
        $currentTenant = Filament::getTenant();

        $select = Select::make($fieldName)
            ->label($label ?? __('eclipse-catalogue::price-list.fields.tenant'))
            ->placeholder($placeholder ?? __('eclipse-catalogue::price-list.placeholders.tenant'))
            ->options($tenants->pluck('name', 'id')->map(function ($name, $id) use ($currentTenant) {
                return $id === $currentTenant?->id
                    ? $name.' ('.__('eclipse-catalogue::price-list.labels.current').')'
                    : $name;
            }))
            ->default($currentTenant?->id)
            ->selectablePlaceholder(false)
            ->live()
            ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                $livewire->dispatch('tenant-changed', $state);
            });

        // Apply custom options
        foreach ($options as $method => $value) {
            if (method_exists($select, $method)) {
                $select->$method($value);
            }
        }

        return $select;
    }
}
