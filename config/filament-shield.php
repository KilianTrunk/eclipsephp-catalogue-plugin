<?php

use Eclipse\Catalogue\Filament\Resources\CategoryResource;
use Eclipse\Catalogue\Filament\Resources\GroupResource;
use Eclipse\Catalogue\Filament\Resources\MeasureUnitResource;
use Eclipse\Catalogue\Filament\Resources\PriceListResource;
use Eclipse\Catalogue\Filament\Resources\ProductResource;
use Eclipse\Catalogue\Filament\Resources\ProductStatusResource;
use Eclipse\Catalogue\Filament\Resources\ProductTypeResource;
use Eclipse\Catalogue\Filament\Resources\PropertyResource;
use Eclipse\Catalogue\Filament\Resources\PropertyValueResource;
use Eclipse\Catalogue\Filament\Resources\TaxClassResource;

return [
    'shield_resource' => [
        'slug' => 'shield/roles',
        'show_model_path' => true,
        'cluster' => null,
        'tabs' => [
            'pages' => true,
            'widgets' => true,
            'resources' => true,
            'custom_permissions' => true,
        ],
    ],

    'tenant_model' => \Eclipse\Core\Models\Site::class,

    'auth_provider_model' => \Eclipse\Core\Models\User::class,

    'super_admin' => [
        'enabled' => true,
        'name' => 'super_admin',
        'define_via_gate' => false,
        'intercept_gate' => 'before',
    ],

    'panel_user' => [
        'enabled' => true,
        'name' => 'panel_user',
    ],

    'permissions' => [
        'separator' => '_',
        'case' => 'lower_snake',
        'generate' => true,
    ],

    'policies' => [
        'path' => app_path('Policies'),
        'merge' => true,
        'generate' => true,
        'methods' => [
            'viewAny', 'view', 'create', 'update', 'restore', 'restoreAny',
            'replicate', 'reorder', 'delete', 'deleteAny', 'forceDelete', 'forceDeleteAny',
        ],
        'single_parameter_methods' => [
            'viewAny', 'create', 'deleteAny', 'forceDeleteAny', 'restoreAny', 'reorder',
        ],
    ],

    'localization' => [
        'enabled' => false,
        'key' => 'filament-shield::filament-shield',
    ],

    'resources' => [
        'subject' => 'model',
        'manage' => [
            ProductResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'restore',
                'restoreAny',
                'delete',
                'deleteAny',
                'forceDelete',
                'forceDeleteAny',
            ],
            CategoryResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'restore',
                'restoreAny',
                'delete',
                'deleteAny',
                'forceDelete',
                'forceDeleteAny',
            ],
            ProductTypeResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'restore',
                'restoreAny',
                'delete',
                'deleteAny',
                'forceDelete',
                'forceDeleteAny',
            ],
            PropertyResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'delete',
                'deleteAny',
                'forceDelete',
                'forceDeleteAny',
                'restore',
                'restoreAny',
            ],
            PropertyValueResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'delete',
                'deleteAny',
            ],
            GroupResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'delete',
                'deleteAny',
            ],
            PriceListResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'restore',
                'restoreAny',
                'delete',
                'deleteAny',
                'forceDelete',
                'forceDeleteAny',
            ],
            MeasureUnitResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'restore',
                'restoreAny',
                'delete',
                'deleteAny',
                'forceDelete',
                'forceDeleteAny',
            ],
            TaxClassResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'restore',
                'restoreAny',
                'delete',
                'deleteAny',
                'forceDelete',
                'forceDeleteAny',
            ],
            ProductStatusResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'delete',
                'deleteAny',
            ],
        ],
        'exclude' => [],
    ],

    'pages' => [
        'subject' => 'class',
        'prefix' => 'view',
        'exclude' => [
        ],
    ],

    'widgets' => [
        'subject' => 'class',
        'prefix' => 'view',
        'exclude' => [
        ],
    ],

    'custom_permissions' => [
    ],

    'discovery' => [
        'discover_all_resources' => false,
        'discover_all_widgets' => false,
        'discover_all_pages' => false,
    ],

    'register_role_policy' => true,
];
