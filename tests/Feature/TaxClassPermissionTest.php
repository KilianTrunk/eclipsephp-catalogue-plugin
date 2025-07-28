<?php

use Eclipse\Catalogue\Models\TaxClass;
use Eclipse\Catalogue\Policies\TaxClassPolicy;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->migrate();
});

it('policy prevents deletion of default class regardless of user permissions', function () {
    $user = User::factory()->create();

    $defaultClass = TaxClass::create([
        'name' => 'Standard Rate',
        'description' => 'Standard tax rate',
        'rate' => 20.00,
        'is_default' => true,
    ]);

    $policy = new TaxClassPolicy;
    $canDelete = $policy->delete($user, $defaultClass);

    expect($canDelete)->toBeFalse();
});

it('policy prevents force deletion of default class regardless of user permissions', function () {
    $user = User::factory()->create();

    $defaultClass = TaxClass::create([
        'name' => 'Standard Rate',
        'description' => 'Standard tax rate',
        'rate' => 20.00,
        'is_default' => true,
    ]);

    $policy = new TaxClassPolicy;
    $canForceDelete = $policy->forceDelete($user, $defaultClass);

    expect($canForceDelete)->toBeFalse();
});
