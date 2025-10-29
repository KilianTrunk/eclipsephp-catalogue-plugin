<?php

declare(strict_types=1);

namespace Workbench\App\Policies;

use Eclipse\Catalogue\Models\Product;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ProductPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_product');
    }

    public function view(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('view_product');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_product');
    }

    public function update(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('update_product');
    }

    public function restore(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('restore_product');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_product');
    }

    public function replicate(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('replicate_product');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_product');
    }

    public function delete(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('delete_product');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any_product');
    }

    public function forceDelete(AuthUser $authUser, Product $product): bool
    {
        return $authUser->can('force_delete_product');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_product');
    }
}
