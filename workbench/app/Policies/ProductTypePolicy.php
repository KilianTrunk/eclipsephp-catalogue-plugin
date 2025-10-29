<?php

declare(strict_types=1);

namespace Workbench\App\Policies;

use Eclipse\Catalogue\Models\ProductType;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ProductTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_product_type');
    }

    public function view(AuthUser $authUser, ProductType $productType): bool
    {
        return $authUser->can('view_product_type');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_product_type');
    }

    public function update(AuthUser $authUser, ProductType $productType): bool
    {
        return $authUser->can('update_product_type');
    }

    public function restore(AuthUser $authUser, ProductType $productType): bool
    {
        return $authUser->can('restore_product_type');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_product_type');
    }

    public function replicate(AuthUser $authUser, ProductType $productType): bool
    {
        return $authUser->can('replicate_product_type');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_product_type');
    }

    public function delete(AuthUser $authUser, ProductType $productType): bool
    {
        return $authUser->can('delete_product_type');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any_product_type');
    }

    public function forceDelete(AuthUser $authUser, ProductType $productType): bool
    {
        return $authUser->can('force_delete_product_type');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_product_type');
    }
}
