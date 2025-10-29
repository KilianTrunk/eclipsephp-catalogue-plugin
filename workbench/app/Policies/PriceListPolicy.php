<?php

declare(strict_types=1);

namespace Workbench\App\Policies;

use Eclipse\Catalogue\Models\PriceList;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PriceListPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_price_list');
    }

    public function view(AuthUser $authUser, PriceList $priceList): bool
    {
        return $authUser->can('view_price_list');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_price_list');
    }

    public function update(AuthUser $authUser, PriceList $priceList): bool
    {
        return $authUser->can('update_price_list');
    }

    public function restore(AuthUser $authUser, PriceList $priceList): bool
    {
        return $authUser->can('restore_price_list');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_price_list');
    }

    public function replicate(AuthUser $authUser, PriceList $priceList): bool
    {
        return $authUser->can('replicate_price_list');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_price_list');
    }

    public function delete(AuthUser $authUser, PriceList $priceList): bool
    {
        return $authUser->can('delete_price_list');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any_price_list');
    }

    public function forceDelete(AuthUser $authUser, PriceList $priceList): bool
    {
        return $authUser->can('force_delete_price_list');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_price_list');
    }
}
