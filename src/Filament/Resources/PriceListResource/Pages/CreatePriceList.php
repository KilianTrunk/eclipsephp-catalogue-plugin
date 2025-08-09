<?php

namespace Eclipse\Catalogue\Filament\Resources\PriceListResource\Pages;

use Eclipse\Catalogue\Filament\Resources\PriceListResource;
use Eclipse\Catalogue\Models\PriceList;
use Eclipse\Catalogue\Traits\HandlesPriceListTenantData;
use Eclipse\Catalogue\Traits\HasPriceListForm;
use Eclipse\Catalogue\Traits\HasTenantFields;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePriceList extends CreateRecord
{
    use HandlesPriceListTenantData, HasPriceListForm, HasTenantFields;

    protected static string $resource = PriceListResource::class;

    public function form(Form $form): Form
    {
        return $form->schema($this->buildPriceListFormSchema());
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Extract tenant data from form
        $tenantData = $this->extractTenantDataFromFormData($data);

        // Clean main record data
        $priceListData = $this->cleanFormDataForMainRecord($data);

        // Use the model's createWithTenantData method
        return PriceList::createWithTenantData($priceListData, $tenantData);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->action(function () {
                    // Store current tenant data before validation/saving
                    $this->storeCurrentTenantData();
                    $this->validateDefaultConstraintsBeforeSave();
                    $this->create();
                }),
            $this->getCancelFormAction(),
        ];
    }

    /**
     * Store current tenant data before submitting
     */
    protected function storeCurrentTenantData(): void
    {
        $formData = $this->form->getState();
        $selectedTenant = $formData['selected_tenant'] ?? null;

        if ($selectedTenant && config('eclipse-catalogue.tenancy.foreign_key')) {
            $currentData = [
                'is_active' => $formData['tenant_data'][$selectedTenant]['is_active'] ?? true,
                'is_default' => $formData['tenant_data'][$selectedTenant]['is_default'] ?? false,
                'is_default_purchase' => $formData['tenant_data'][$selectedTenant]['is_default_purchase'] ?? false,
            ];

            $allTenantData = $formData['all_tenant_data'] ?? [];
            $allTenantData[$selectedTenant] = $currentData;

            $this->form->fill(['all_tenant_data' => $allTenantData]);
        }
    }
}
