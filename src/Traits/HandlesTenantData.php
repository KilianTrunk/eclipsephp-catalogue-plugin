<?php

namespace Eclipse\Catalogue\Traits;

trait HandlesTenantData
{
    /**
     * Get the tenant flags for this form.
     * Override this method in your page class to customize behavior.
     */
    protected function getFormTenantFlags(): array
    {
        return $this->formTenantFlags ?? ['is_active'];
    }

    /**
     * Get the mutually exclusive flag sets for this form.
     * Override this method in your page class to customize behavior.
     */
    protected function getFormMutuallyExclusiveFlagSets(): array
    {
        return $this->formMutuallyExclusiveFlagSets ?? [];
    }

    /**
     * Persist the currently selected tenant's values into the hidden
     * all_tenant_data state so switching tenants doesn't lose changes.
     */
    protected function storeCurrentTenantData(): void
    {
        $formData = $this->form->getState();
        $selectedTenant = $formData['selected_tenant'] ?? null;

        if ($selectedTenant && config('eclipse-catalogue.tenancy.foreign_key')) {
            $currentData = [];

            // Build current data from tenant flags
            foreach ($this->getFormTenantFlags() as $flag) {
                $currentData[$flag] = $formData['tenant_data'][$selectedTenant][$flag] ?? $this->getDefaultValueForFlag($flag);
            }

            $allTenantData = $formData['all_tenant_data'] ?? [];
            $allTenantData[$selectedTenant] = $currentData;

            $this->form->fill(['all_tenant_data' => $allTenantData]);
        }
    }

    /**
     * Validate default constraints before saving.
     */
    protected function validateDefaultConstraintsBeforeSave(): void
    {
        $data = $this->form->getState();
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy - validate simple fields
            $tenantData = [];
            foreach ($this->getFormTenantFlags() as $flag) {
                $tenantData[$flag] = $data[$flag] ?? $this->getDefaultValueForFlag($flag);
            }

            $this->validateTenantDataConstraints($tenantData);

            return;
        }

        // Validate tenant data (multi-tenant)
        $tenantData = $data['tenant_data'] ?? [];
        $this->validateTenantDataConstraints($tenantData);

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
            foreach ($this->getFormMutuallyExclusiveFlagSets() as $exclusiveSet) {
                $activeFlags = array_filter($exclusiveSet, fn ($flag) => $tenantSpecificData[$flag] ?? false);
                if (count($activeFlags) > 1) {
                    return $tenantId;
                }
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
            $result = [];
            foreach ($this->getFormTenantFlags() as $flag) {
                $result[$flag] = $data[$flag] ?? $this->getDefaultValueForFlag($flag);
            }

            return $result;
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
     * the remaining payload to the base model.
     */
    protected function cleanFormDataForMainRecord(array $data): array
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // Remove simple tenant fields
            foreach ($this->getFormTenantFlags() as $flag) {
                unset($data[$flag]);
            }
        } else {
            // Remove tenant UI fields
            unset($data['tenant_data'], $data['selected_tenant'], $data['all_tenant_data']);
        }

        return $data;
    }

    /**
     * Validate tenant data constraints.
     */
    protected function validateTenantDataConstraints(array $tenantData): void
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy - validate simple fields
            foreach ($this->getFormMutuallyExclusiveFlagSets() as $exclusiveSet) {
                $activeFlags = array_filter($exclusiveSet, fn ($flag) => $tenantData[$flag] ?? false);

                if (count($activeFlags) > 1) {
                    $errors = [];
                    foreach ($activeFlags as $flag) {
                        $errors[$flag] = 'These options cannot be enabled simultaneously.';
                    }
                    throw \Illuminate\Validation\ValidationException::withMessages($errors);
                }
            }

            return;
        }

        // Validate tenant data
        $errors = [];
        $firstErrorTenantId = null;

        foreach ($tenantData as $tenantId => $tenantSpecificData) {
            foreach ($this->getFormMutuallyExclusiveFlagSets() as $exclusiveSet) {
                $activeFlags = array_filter($exclusiveSet, fn ($flag) => $tenantSpecificData[$flag] ?? false);

                if (count($activeFlags) > 1) {
                    $tenantModel = config('eclipse-catalogue.tenancy.model');
                    $tenant = $tenantModel::find($tenantId);
                    $tenantName = $tenant ? $tenant->name : "Tenant {$tenantId}";

                    if (! $firstErrorTenantId) {
                        $firstErrorTenantId = $tenantId;
                    }

                    foreach ($activeFlags as $flag) {
                        $errors["tenant_data.{$tenantId}.{$flag}"] = "These options cannot be enabled simultaneously for {$tenantName}.";
                    }
                }
            }
        }

        if (! empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    /**
     * Get default value for a specific flag.
     */
    protected function getDefaultValueForFlag(string $flag): bool
    {
        return in_array($flag, ['is_active']) ? true : false;
    }
}
