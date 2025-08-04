<?php

namespace Eclipse\Catalogue\Filament\Resources\PriceListResource\Pages;

use Eclipse\Catalogue\Filament\Resources\PriceListResource;
use Eclipse\Catalogue\Forms\Components\TenantFieldsComponent;
use Eclipse\Catalogue\Models\PriceListData;
use Eclipse\Catalogue\Traits\HasTenantFields;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePriceList extends CreateRecord
{
    use HasTenantFields;

    protected static string $resource = PriceListResource::class;

    public function form(Form $form): Form
    {
        $baseSchema = [
            \Filament\Forms\Components\Section::make(__('eclipse-catalogue::price-list.sections.information'))
                ->description(__('eclipse-catalogue::price-list.sections.information_description'))
                ->schema([
                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('name')
                                ->label(__('eclipse-catalogue::price-list.fields.name'))
                                ->required()
                                ->maxLength(255)
                                ->placeholder(__('eclipse-catalogue::price-list.placeholders.name')),

                            \Filament\Forms\Components\TextInput::make('code')
                                ->label(__('eclipse-catalogue::price-list.fields.code'))
                                ->maxLength(255)
                                ->placeholder(__('eclipse-catalogue::price-list.placeholders.code'))
                                ->unique(
                                    table: 'pim_price_lists',
                                    column: 'code',
                                    ignoreRecord: true
                                ),
                        ]),

                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\Select::make('currency_id')
                                ->label(__('eclipse-catalogue::price-list.fields.currency'))
                                ->relationship('currency', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->placeholder(__('eclipse-catalogue::price-list.placeholders.currency')),

                            \Filament\Forms\Components\Toggle::make('tax_included')
                                ->label(__('eclipse-catalogue::price-list.fields.tax_included'))
                                ->helperText(__('eclipse-catalogue::price-list.help_text.tax_included'))
                                ->default(false)
                                ->inline(false),
                        ]),

                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label(__('eclipse-catalogue::price-list.fields.notes'))
                        ->placeholder(__('eclipse-catalogue::price-list.placeholders.notes'))
                        ->rows(3)
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->persistCollapsed(),
        ];

        // Add tenant fields if tenancy is enabled
        if ($this->isTenancyEnabled()) {
            $baseSchema[] = TenantFieldsComponent::make();
        } else {
            // No tenancy - add simple settings section
            $baseSchema[] = \Filament\Forms\Components\Section::make(__('eclipse-catalogue::price-list.sections.settings'))
                ->description(__('eclipse-catalogue::price-list.sections.settings_description'))
                ->schema([
                    \Filament\Forms\Components\Toggle::make('is_active')
                        ->label(__('eclipse-catalogue::price-list.fields.is_active'))
                        ->helperText(__('eclipse-catalogue::price-list.help_text.is_active'))
                        ->default(true)
                        ->inline(false),

                    \Filament\Forms\Components\Fieldset::make(__('eclipse-catalogue::price-list.sections.default_settings'))
                        ->schema([
                            \Filament\Forms\Components\Toggle::make('is_default')
                                ->label(__('eclipse-catalogue::price-list.fields.is_default'))
                                ->helperText(__('eclipse-catalogue::price-list.help_text.is_default'))
                                ->inline(false)
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($state && $get('is_default_purchase')) {
                                        $set('is_default_purchase', false);
                                        \Filament\Notifications\Notification::make()
                                            ->warning()
                                            ->title(__('eclipse-catalogue::price-list.notifications.conflict_resolved_title'))
                                            ->body(__('eclipse-catalogue::price-list.notifications.conflict_resolved_purchase_disabled_simple'))
                                            ->send();
                                    }
                                }),

                            \Filament\Forms\Components\Toggle::make('is_default_purchase')
                                ->label(__('eclipse-catalogue::price-list.fields.is_default_purchase'))
                                ->helperText(__('eclipse-catalogue::price-list.help_text.is_default_purchase'))
                                ->inline(false)
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($state && $get('is_default')) {
                                        $set('is_default', false);
                                        \Filament\Notifications\Notification::make()
                                            ->warning()
                                            ->title(__('eclipse-catalogue::price-list.notifications.conflict_resolved_title'))
                                            ->body(__('eclipse-catalogue::price-list.notifications.conflict_resolved_selling_disabled_simple'))
                                            ->send();
                                    }
                                }),
                        ])
                        ->columns(1),
                ])
                ->collapsible()
                ->persistCollapsed();
        }

        return $form->schema($baseSchema);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy - simple creation
            $priceListDataFields = [
                'is_active' => $data['is_active'] ?? true,
                'is_default' => $data['is_default'] ?? false,
                'is_default_purchase' => $data['is_default_purchase'] ?? false,
            ];

            unset($data['is_active'], $data['is_default'], $data['is_default_purchase']);

            $this->handleDefaultConstraints($priceListDataFields, null);

            $record = static::getModel()::create($data);

            PriceListData::create([
                'price_list_id' => $record->id,
                ...$priceListDataFields,
            ]);

            return $record;
        }

        // Extract tenant data and UI fields
        $tenantData = $data['tenant_data'] ?? [];
        unset($data['tenant_data'], $data['selected_tenant']);

        // Create the main price list record
        $record = static::getModel()::create($data);

        // Create tenant-specific data
        if (! empty($tenantData)) {
            foreach ($tenantData as $tenantId => $tenantSpecificData) {
                // Handle default constraints
                $this->handleDefaultConstraints($tenantSpecificData, $tenantId);

                PriceListData::create([
                    'price_list_id' => $record->id,
                    $tenantFK => $tenantId,
                    'is_active' => $tenantSpecificData['is_active'] ?? true,
                    'is_default' => $tenantSpecificData['is_default'] ?? false,
                    'is_default_purchase' => $tenantSpecificData['is_default_purchase'] ?? false,
                ]);
            }
        } else {
            // Create default records for all tenants
            $tenantModel = config('eclipse-catalogue.tenancy.model');
            $tenants = $tenantModel::all();

            foreach ($tenants as $tenant) {
                PriceListData::create([
                    'price_list_id' => $record->id,
                    $tenantFK => $tenant->id,
                    'is_active' => true,
                    'is_default' => false,
                    'is_default_purchase' => false,
                ]);
            }
        }

        return $record;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->action(function () {
                    $this->validateDefaultConstraintsBeforeSave();
                    $this->create();
                }),
            $this->getCancelFormAction(),
        ];
    }

    protected function validateDefaultConstraintsBeforeSave(): void
    {
        $data = $this->form->getState();
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy - validate simple fields
            if (($data['is_default'] ?? false) && ($data['is_default_purchase'] ?? false)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'is_default' => __('eclipse-catalogue::price-list.validation.cannot_be_both_defaults'),
                    'is_default_purchase' => __('eclipse-catalogue::price-list.validation.cannot_be_both_defaults'),
                ]);
            }

            return;
        }

        // Validate tenant data
        $tenantData = $data['tenant_data'] ?? [];
        $firstErrorTenantId = null;
        $errors = [];

        foreach ($tenantData as $tenantId => $tenantSpecificData) {
            if (
                ($tenantSpecificData['is_default'] ?? false) &&
                ($tenantSpecificData['is_default_purchase'] ?? false)
            ) {
                $tenantModel = config('eclipse-catalogue.tenancy.model');
                $tenant = $tenantModel::find($tenantId);
                $tenantName = $tenant ? $tenant->name : "Tenant {$tenantId}";

                if (! $firstErrorTenantId) {
                    $firstErrorTenantId = $tenantId;
                }

                $errors["tenant_data.{$tenantId}.is_default"] = __('eclipse-catalogue::price-list.validation.cannot_be_both_defaults_tenant', ['tenant' => $tenantName]);
                $errors["tenant_data.{$tenantId}.is_default_purchase"] = __('eclipse-catalogue::price-list.validation.cannot_be_both_defaults_tenant', ['tenant' => $tenantName]);
            }
        }

        if (! empty($errors)) {
            // Switch to first tenant with errors
            if ($firstErrorTenantId) {
                $this->form->fill(['selected_tenant' => $firstErrorTenantId]);
            }

            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    private function handleDefaultConstraints(array &$tenantData, ?int $tenantId): void
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        // Validate that a price list cannot be both default selling and purchase
        if (($tenantData['is_default'] ?? false) && ($tenantData['is_default_purchase'] ?? false)) {
            $errorKey = $tenantId ? "tenant_data.{$tenantId}" : '';
            throw \Illuminate\Validation\ValidationException::withMessages([
                "{$errorKey}.is_default" => 'A price list cannot be both default selling and default purchase.',
                "{$errorKey}.is_default_purchase" => 'A price list cannot be both default selling and default purchase.',
            ]);
        }

        // If setting as default selling, unset other defaults for this tenant
        if ($tenantData['is_default'] ?? false) {
            $query = PriceListData::where('is_default', true);
            if ($tenantFK && $tenantId) {
                $query->where($tenantFK, $tenantId);
            }
            $query->update(['is_default' => false]);
        }

        // If setting as default purchase, unset other defaults for this tenant
        if ($tenantData['is_default_purchase'] ?? false) {
            $query = PriceListData::where('is_default_purchase', true);
            if ($tenantFK && $tenantId) {
                $query->where($tenantFK, $tenantId);
            }
            $query->update(['is_default_purchase' => false]);
        }
    }
}
