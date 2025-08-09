<?php

namespace Eclipse\Catalogue\Traits;

use Eclipse\Catalogue\Models\PriceList;

trait HandlesPriceListTenantData
{
    /**
     * Validate default constraints before saving.
     */
    protected function validateDefaultConstraintsBeforeSave(): void
    {
        $data = $this->form->getState();
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy - validate simple fields
            $tenantData = [
                'is_default' => $data['is_default'] ?? false,
                'is_default_purchase' => $data['is_default_purchase'] ?? false,
            ];
            PriceList::validateTenantDataConstraints($tenantData);

            return;
        }

        // Validate tenant data (multi-tenant)
        $tenantData = $data['tenant_data'] ?? [];
        PriceList::validateTenantDataConstraints($tenantData);

        // Handle form state for first error tenant (for UI feedback)
        $firstErrorTenantId = $this->getFirstErrorTenantId($tenantData);
        if ($firstErrorTenantId) {
            $this->form->fill(['selected_tenant' => $firstErrorTenantId]);
        }
    }

    /**
     * Get the first tenant ID that has validation errors.
     */
    private function getFirstErrorTenantId(array $tenantData): ?int
    {
        foreach ($tenantData as $tenantId => $tenantSpecificData) {
            if (
                ($tenantSpecificData['is_default'] ?? false) &&
                ($tenantSpecificData['is_default_purchase'] ?? false)
            ) {
                return $tenantId;
            }
        }

        return null;
    }

    /**
     * Extract per-tenant data from the form state.
     */
    protected function extractTenantDataFromFormData(array $data): array
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy - return simple fields
            return [
                'is_active' => $data['is_active'] ?? true,
                'is_default' => $data['is_default'] ?? false,
                'is_default_purchase' => $data['is_default_purchase'] ?? false,
            ];
        }

        // Get stored tenant data and current form data
        $storedTenantData = $data['all_tenant_data'] ?? [];
        $currentTenantData = $data['tenant_data'] ?? [];
        $selectedTenant = $data['selected_tenant'] ?? null;

        // Merge stored data with current form data
        $result = $storedTenantData;

        // If we have a selected tenant, store its current form data
        if ($selectedTenant && isset($currentTenantData[$selectedTenant])) {
            $result[$selectedTenant] = $currentTenantData[$selectedTenant];
        }

        return $result;
    }

    /**
     * Remove UI-only fields from the form values so we can safely pass
     * the remaining payload to the base PriceList model.
     */
    protected function cleanFormDataForMainRecord(array $data): array
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // Remove simple tenant fields
            unset($data['is_active'], $data['is_default'], $data['is_default_purchase']);
        } else {
            // Remove tenant UI fields
            unset($data['tenant_data'], $data['selected_tenant'], $data['all_tenant_data']);
        }

        return $data;
    }
}
