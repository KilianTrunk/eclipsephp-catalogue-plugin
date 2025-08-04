<?php

namespace Eclipse\Catalogue\Filament\Resources\PriceListResource\Pages;

use Eclipse\Catalogue\Filament\Resources\PriceListResource;
use Eclipse\Catalogue\Models\PriceListData;
use Eclipse\Catalogue\Traits\HasPriceListForm;
use Eclipse\Catalogue\Traits\HasTenantFields;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreatePriceList extends CreateRecord
{
    use HasPriceListForm, HasTenantFields;

    protected static string $resource = PriceListResource::class;

    public function form(Form $form): Form
    {
        return $form->schema($this->buildPriceListFormSchema());
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

        // Create tenant-specific data for ALL tenants
        $tenantModel = config('eclipse-catalogue.tenancy.model');
        $tenants = $tenantModel::all();

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;
            $tenantSpecificData = $tenantData[$tenantId] ?? [
                'is_active' => true,
                'is_default' => false,
                'is_default_purchase' => false,
            ];

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
                throw ValidationException::withMessages([
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

            throw ValidationException::withMessages($errors);
        }
    }

    private function handleDefaultConstraints(array &$tenantData, ?int $tenantId): void
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        // Validate that a price list cannot be both default selling and purchase
        if (($tenantData['is_default'] ?? false) && ($tenantData['is_default_purchase'] ?? false)) {
            $errorKey = $tenantId ? "tenant_data.{$tenantId}" : '';
            throw ValidationException::withMessages([
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
