<?php

namespace Eclipse\Catalogue\Forms\Components;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;

class GenericTenantFieldsComponent
{
    protected array $tenantFlags;

    protected array $mutuallyExclusiveFlagSets;

    protected string $translationPrefix;

    protected string $sectionTitle;

    protected string $sectionDescription;

    public function __construct(
        array $tenantFlags = ['is_active'],
        array $mutuallyExclusiveFlagSets = [],
        string $translationPrefix = 'eclipse-catalogue::product-type',
        string $sectionTitle = 'Tenant Settings',
        string $sectionDescription = 'Configure settings for each tenant'
    ) {
        $this->tenantFlags = $tenantFlags;
        $this->mutuallyExclusiveFlagSets = $mutuallyExclusiveFlagSets;
        $this->translationPrefix = $translationPrefix;
        $this->sectionTitle = $sectionTitle;
        $this->sectionDescription = $sectionDescription;
    }

    public static function make(
        array $tenantFlags = ['is_active'],
        array $mutuallyExclusiveFlagSets = [],
        string $translationPrefix = 'eclipse-catalogue::product-type',
        string $sectionTitle = 'Tenant Settings',
        string $sectionDescription = 'Configure settings for each tenant'
    ): Component {
        $instance = new static($tenantFlags, $mutuallyExclusiveFlagSets, $translationPrefix, $sectionTitle, $sectionDescription);

        return $instance->build();
    }

    public function build(): Component
    {
        $tenantModel = config('eclipse-catalogue.tenancy.model');

        if (! $tenantModel) {
            return Grid::make(1)->schema([]);
        }

        $tenants = $tenantModel::all();

        return Section::make(__($this->translationPrefix.'.sections.tenant_settings') ?: $this->sectionTitle)
            ->description(__($this->translationPrefix.'.sections.tenant_settings_description') ?: $this->sectionDescription)
            ->schema([
                $this->makeTenantSwitcher(),

                // Hidden field to store all tenant data
                \Filament\Forms\Components\Hidden::make('all_tenant_data')
                    ->default([])
                    ->dehydrated(true),

                // Hidden field to track previous tenant for switching logic
                \Filament\Forms\Components\Hidden::make('_previous_tenant')
                    ->default(\Filament\Facades\Filament::getTenant()?->id)
                    ->dehydrated(false), // Don't submit this field

                ...$this->getTenantSpecificFields($tenants),
            ])
            ->collapsible()
            ->persistCollapsed();
    }

    protected function makeTenantSwitcher(): Component
    {
        $tenantModel = config('eclipse-catalogue.tenancy.model');

        if (! $tenantModel) {
            return \Filament\Forms\Components\Select::make('selected_tenant')->hidden();
        }

        $tenants = $tenantModel::all();
        $currentTenant = \Filament\Facades\Filament::getTenant();

        return \Filament\Forms\Components\Select::make('selected_tenant')
            ->label(__($this->translationPrefix.'.fields.tenant') ?: 'Tenant')
            ->placeholder(__($this->translationPrefix.'.placeholders.tenant') ?: 'Select tenant')
            ->options($tenants->pluck('name', 'id')->map(function ($name, $id) use ($currentTenant) {
                return $id === $currentTenant?->id
                    ? $name.' ('.(__($this->translationPrefix.'.labels.current') ?: 'Current').')'
                    : $name;
            }))
            ->default($currentTenant?->id)
            ->selectablePlaceholder(false)
            ->live()
            ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                // Get previous tenant from a tracking field
                $previousTenant = $get('_previous_tenant');

                // Store current tenant data before switching
                if ($previousTenant && $previousTenant != $state) {
                    // Get current tenant data
                    $currentData = [];
                    foreach ($this->tenantFlags as $flag) {
                        $currentData[$flag] = $get("tenant_data.{$previousTenant}.{$flag}") ?? $this->getDefaultValueForFlag($flag);
                    }

                    // Store it in a persistent field
                    $allTenantData = $get('all_tenant_data') ?? [];
                    $allTenantData[$previousTenant] = $currentData;
                    $set('all_tenant_data', $allTenantData);
                }

                // Update the previous tenant tracker
                $set('_previous_tenant', $state);

                // Load data for new tenant
                $allTenantData = $get('all_tenant_data') ?? [];
                if (isset($allTenantData[$state])) {
                    $tenantData = $allTenantData[$state];
                    foreach ($this->tenantFlags as $flag) {
                        $set("tenant_data.{$state}.{$flag}", $tenantData[$flag] ?? $this->getDefaultValueForFlag($flag));
                    }
                }

                // Trigger form update when tenant changes
                $livewire->dispatch('tenant-changed', $state);
            })
            ->required();
    }

    /**
     * Build the fields that belong to a single tenant.
     */
    public function makeTenantFields(int $tenantId, string $tenantName): array
    {
        $fields = [];

        foreach ($this->tenantFlags as $flag) {
            if ($flag === 'is_active') {
                $fields[] = Toggle::make("tenant_data.{$tenantId}.{$flag}")
                    ->label(__($this->translationPrefix.".fields.{$flag}") ?: ucfirst(str_replace('_', ' ', $flag)))
                    ->helperText(__($this->translationPrefix.".help_text.{$flag}_tenant", ['tenant' => $tenantName]) ?: "Enable for {$tenantName}")
                    ->default($this->getDefaultValueForFlag($flag))
                    ->inline(false)
                    ->dehydrated(true);
            }
        }

        // Group default flags in fieldset if any exist
        $defaultFlags = array_filter($this->tenantFlags, fn ($flag) => str_starts_with($flag, 'is_default'));

        if (! empty($defaultFlags)) {
            $defaultToggles = [];

            foreach ($defaultFlags as $flag) {
                $toggle = Toggle::make("tenant_data.{$tenantId}.{$flag}")
                    ->label(__($this->translationPrefix.".fields.{$flag}") ?: ucfirst(str_replace('_', ' ', $flag)))
                    ->helperText(__($this->translationPrefix.".help_text.{$flag}_tenant", ['tenant' => $tenantName]) ?: "Set as default for {$tenantName}")
                    ->inline(false)
                    ->live()
                    ->dehydrated(true);

                // Add mutual exclusivity logic
                foreach ($this->mutuallyExclusiveFlagSets as $exclusiveSet) {
                    if (in_array($flag, $exclusiveSet)) {
                        $otherFlags = array_diff($exclusiveSet, [$flag]);
                        $toggle->afterStateUpdated(function ($state, callable $set, callable $get) use ($tenantId, $tenantName, $otherFlags) {
                            if ($state) {
                                foreach ($otherFlags as $otherFlag) {
                                    if ($get("tenant_data.{$tenantId}.{$otherFlag}")) {
                                        $set("tenant_data.{$tenantId}.{$otherFlag}", false);
                                        Notification::make()
                                            ->warning()
                                            ->title(__($this->translationPrefix.'.notifications.conflict_resolved_title') ?: 'Conflict Resolved')
                                            ->body(__($this->translationPrefix.".notifications.conflict_resolved_{$otherFlag}_disabled", ['tenant' => $tenantName]) ?: "Disabled conflicting option for {$tenantName}")
                                            ->send();
                                    }
                                }
                            }
                        });
                        break;
                    }
                }

                $defaultToggles[] = $toggle;
            }

            if (! empty($defaultToggles)) {
                $fields[] = Fieldset::make(__($this->translationPrefix.'.sections.default_settings') ?: 'Default Settings')
                    ->schema($defaultToggles)
                    ->columns(1);
            }
        }

        return $fields;
    }

    /**
     * Wrap each tenant's fields in a Grid and show only the selected tenant's
     * fields. Values are still dehydrated via per-field settings.
     */
    protected function getTenantSpecificFields($tenants): array
    {
        $fields = [];

        foreach ($tenants as $tenant) {
            $fields[] = Grid::make(1)
                ->schema($this->makeTenantFields($tenant->id, $tenant->name))
                ->visible(fn (callable $get) => $get('selected_tenant') == $tenant->id);
        }

        return $fields;
    }

    protected function getDefaultValueForFlag(string $flag): bool
    {
        return in_array($flag, ['is_active']) ? true : false;
    }
}
