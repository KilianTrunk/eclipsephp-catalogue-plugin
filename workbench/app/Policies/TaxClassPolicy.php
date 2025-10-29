<?php

declare(strict_types=1);

namespace Workbench\App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Eclipse\Catalogue\Models\TaxClass;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaxClassPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_tax_class');
    }

    public function view(AuthUser $authUser, TaxClass $taxClass): bool
    {
        return $authUser->can('view_tax_class');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_tax_class');
    }

    public function update(AuthUser $authUser, TaxClass $taxClass): bool
    {
        return $authUser->can('update_tax_class');
    }

    public function restore(AuthUser $authUser, TaxClass $taxClass): bool
    {
        return $authUser->can('restore_tax_class');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_tax_class');
    }

    public function replicate(AuthUser $authUser, TaxClass $taxClass): bool
    {
        return $authUser->can('replicate_tax_class');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_tax_class');
    }

    public function delete(AuthUser $authUser, TaxClass $taxClass): bool
    {
        return $authUser->can('delete_tax_class');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any_tax_class');
    }

    public function forceDelete(AuthUser $authUser, TaxClass $taxClass): bool
    {
        return $authUser->can('force_delete_tax_class');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_tax_class');
    }

}