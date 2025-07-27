<?php

use Eclipse\Catalogue\Models\MeasureUnit;
use Eclipse\Catalogue\Policies\MeasureUnitPolicy;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->migrate();
});

it('policy prevents deletion of default unit regardless of user permissions', function () {
    $user = User::factory()->create();

    $defaultUnit = MeasureUnit::create([
        'name' => 'Kilogram',
        'is_default' => true,
    ]);

    $policy = new MeasureUnitPolicy;
    $canDelete = $policy->delete($user, $defaultUnit);

    expect($canDelete)->toBeFalse();
});

it('policy prevents force deletion of default unit regardless of user permissions', function () {
    $user = User::factory()->create();

    $defaultUnit = MeasureUnit::create([
        'name' => 'Kilogram',
        'is_default' => true,
    ]);

    $policy = new MeasureUnitPolicy;
    $canForceDelete = $policy->forceDelete($user, $defaultUnit);

    expect($canForceDelete)->toBeFalse();
});
