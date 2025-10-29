<?php

declare(strict_types=1);

namespace Workbench\App\Policies;

use Eclipse\Catalogue\Models\MeasureUnit;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MeasureUnitPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_measure_unit');
    }

    public function view(AuthUser $authUser, MeasureUnit $measureUnit): bool
    {
        return $authUser->can('view_measure_unit');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_measure_unit');
    }

    public function update(AuthUser $authUser, MeasureUnit $measureUnit): bool
    {
        return $authUser->can('update_measure_unit');
    }

    public function restore(AuthUser $authUser, MeasureUnit $measureUnit): bool
    {
        return $authUser->can('restore_measure_unit');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_measure_unit');
    }

    public function replicate(AuthUser $authUser, MeasureUnit $measureUnit): bool
    {
        return $authUser->can('replicate_measure_unit');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_measure_unit');
    }

    public function delete(AuthUser $authUser, MeasureUnit $measureUnit): bool
    {
        return $authUser->can('delete_measure_unit');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any_measure_unit');
    }

    public function forceDelete(AuthUser $authUser, MeasureUnit $measureUnit): bool
    {
        return $authUser->can('force_delete_measure_unit');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_measure_unit');
    }
}
