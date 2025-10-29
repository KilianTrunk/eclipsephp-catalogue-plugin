<?php

declare(strict_types=1);

namespace Workbench\App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Eclipse\Catalogue\Models\Property;
use Illuminate\Auth\Access\HandlesAuthorization;

class PropertyPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_property');
    }

    public function view(AuthUser $authUser, Property $property): bool
    {
        return $authUser->can('view_property');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_property');
    }

    public function update(AuthUser $authUser, Property $property): bool
    {
        return $authUser->can('update_property');
    }

    public function restore(AuthUser $authUser, Property $property): bool
    {
        return $authUser->can('restore_property');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_property');
    }

    public function replicate(AuthUser $authUser, Property $property): bool
    {
        return $authUser->can('replicate_property');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_property');
    }

    public function delete(AuthUser $authUser, Property $property): bool
    {
        return $authUser->can('delete_property');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any_property');
    }

    public function forceDelete(AuthUser $authUser, Property $property): bool
    {
        return $authUser->can('force_delete_property');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_property');
    }

}