<?php

namespace Eclipse\Catalogue\Filament\Resources\PriceListResource\Pages;

use Eclipse\Catalogue\Filament\Resources\PriceListResource;
use Eclipse\Catalogue\Models\PriceListData;
use Eclipse\Catalogue\Traits\HasPriceListForm;
use Eclipse\Catalogue\Traits\HasTenantFields;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPriceList extends EditRecord
{
    use HasPriceListForm, HasTenantFields;

    protected static string $resource = PriceListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->buildPriceListFormSchema());
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy - load single record
            $priceListData = $this->record->priceListData()->first();

            if ($priceListData) {
                $data['is_active'] = $priceListData->is_active;
                $data['is_default'] = $priceListData->is_default;
                $data['is_default_purchase'] = $priceListData->is_default_purchase;
            }

            return $data;
        }

        // Load tenant-specific data
        $tenantData = [];
        $priceListData = $this->record->priceListData;

        foreach ($priceListData as $tenantRecord) {
            $tenantId = $tenantRecord->getAttribute($tenantFK);
            $tenantData[$tenantId] = [
                'is_active' => $tenantRecord->is_active,
                'is_default' => $tenantRecord->is_default,
                'is_default_purchase' => $tenantRecord->is_default_purchase,
            ];
        }

        $data['tenant_data'] = $tenantData;

        // Set the selected tenant to current tenant so the form shows properly
        $currentTenant = \Filament\Facades\Filament::getTenant();
        $data['selected_tenant'] = $currentTenant?->id;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy - extract simple fields
            $this->priceListDataFields = [
                'is_active' => $data['is_active'] ?? true,
                'is_default' => $data['is_default'] ?? false,
                'is_default_purchase' => $data['is_default_purchase'] ?? false,
            ];

            unset($data['is_active'], $data['is_default'], $data['is_default_purchase']);

            return $data;
        }

        // Extract tenant data and UI fields but don't process it here - do it after save
        $this->tenantDataToProcess = $data['tenant_data'] ?? [];
        unset($data['tenant_data'], $data['selected_tenant']);

        return $data;
    }

    protected $tenantDataToProcess = [];

    protected $priceListDataFields = [];

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Update the main record first
        $record->update($data);

        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy - simple update
            if (! empty($this->priceListDataFields)) {
                $this->handleDefaultConstraints($this->priceListDataFields, null);

                PriceListData::updateOrCreate(
                    ['price_list_id' => $record->id],
                    $this->priceListDataFields
                );
            }

            return $record;
        }

        // Update tenant-specific data for ALL tenants
        $tenantModel = config('eclipse-catalogue.tenancy.model');
        $tenants = $tenantModel::all();

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;
            $tenantSpecificData = $this->tenantDataToProcess[$tenantId] ?? [
                'is_active' => true,
                'is_default' => false,
                'is_default_purchase' => false,
            ];

            // Handle default constraints
            $this->handleDefaultConstraints($tenantSpecificData, $tenantId);

            PriceListData::updateOrCreate(
                [
                    'price_list_id' => $record->id,
                    $tenantFK => $tenantId,
                ],
                [
                    'is_active' => $tenantSpecificData['is_active'] ?? true,
                    'is_default' => $tenantSpecificData['is_default'] ?? false,
                    'is_default_purchase' => $tenantSpecificData['is_default_purchase'] ?? false,
                ]
            );
        }

        // Clear the processed data
        $this->tenantDataToProcess = [];

        return $record;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->action(function () {
                    $this->validateDefaultConstraintsBeforeSave();
                    $this->save();
                }),
            $this->getCancelFormAction(),
        ];
    }

    protected function validateDefaultConstraintsBeforeSave(): void
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy - validate simple fields
            if (($this->priceListDataFields['is_default'] ?? false) &&
                ($this->priceListDataFields['is_default_purchase'] ?? false)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'is_default' => __('eclipse-catalogue::price-list.validation.cannot_be_both_defaults'),
                    'is_default_purchase' => __('eclipse-catalogue::price-list.validation.cannot_be_both_defaults'),
                ]);
            }

            return;
        }

        // Validate tenant data
        $firstErrorTenantId = null;
        $errors = [];

        foreach ($this->tenantDataToProcess as $tenantId => $tenantData) {
            if (($tenantData['is_default'] ?? false) && ($tenantData['is_default_purchase'] ?? false)) {
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
                $errorKey.'.is_default' => 'A price list cannot be both default selling and default purchase.',
                $errorKey.'.is_default_purchase' => 'A price list cannot be both default selling and default purchase.',
            ]);
        }

        // If setting as default selling, unset other defaults for this tenant
        if ($tenantData['is_default'] ?? false) {
            $query = PriceListData::where('is_default', true)
                ->where('price_list_id', '!=', $this->getRecord()->id);

            if ($tenantFK && $tenantId) {
                $query->where($tenantFK, $tenantId);
            }

            $query->update(['is_default' => false]);
        }

        // If setting as default purchase, unset other defaults for this tenant
        if ($tenantData['is_default_purchase'] ?? false) {
            $query = PriceListData::where('is_default_purchase', true)
                ->where('price_list_id', '!=', $this->getRecord()->id);

            if ($tenantFK && $tenantId) {
                $query->where($tenantFK, $tenantId);
            }

            $query->update(['is_default_purchase' => false]);
        }
    }

    protected function resolveRecord($key): Model
    {
        // Load record without joins to avoid ambiguous column issues
        return static::getResource()::getModel()::withoutGlobalScopes()->findOrFail($key);
    }
}
