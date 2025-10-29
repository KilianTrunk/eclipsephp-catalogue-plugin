<?php

declare(strict_types=1);

namespace Workbench\App\Policies;

use Eclipse\Catalogue\Models\ProductStatus;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ProductStatusPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_product_status');
    }

    public function view(AuthUser $authUser, ProductStatus $productStatus): bool
    {
        return $authUser->can('view_product_status');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_product_status');
    }

    public function update(AuthUser $authUser, ProductStatus $productStatus): bool
    {
        return $authUser->can('update_product_status');
    }

    public function restore(AuthUser $authUser, ProductStatus $productStatus): bool
    {
        return $authUser->can('restore_product_status');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_product_status');
    }

    public function replicate(AuthUser $authUser, ProductStatus $productStatus): bool
    {
        return $authUser->can('replicate_product_status');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_product_status');
    }

    public function delete(AuthUser $authUser, ProductStatus $productStatus): bool
    {
        return $authUser->can('delete_product_status');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any_product_status');
    }

    public function forceDelete(AuthUser $authUser, ProductStatus $productStatus): bool
    {
        return $authUser->can('force_delete_product_status');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_product_status');
    }
}
