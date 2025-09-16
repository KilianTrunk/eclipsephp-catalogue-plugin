<?php

use Eclipse\Catalogue\Filament\Resources\ProductResource;
use Eclipse\Catalogue\Models\Category;
use Eclipse\Catalogue\Models\Group;
use Eclipse\Catalogue\Models\PriceList;
use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\ProductData;
use Eclipse\Catalogue\Services\ProductBulkUpdater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->migrate();
    $this->setUpSuperAdminAndTenant();
});

it('exposes bulk update action on product list (opens without errors)', function (): void {
    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->assertTableBulkActionExists('bulk_update');
});

it('respects no-change vs blank update for category and toggle fields', function (): void {
    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key', 'site_id');
    $siteId = \Workbench\App\Models\Site::first()->id;

    $catA = Category::factory()->create();
    $product = Product::factory()->create();

    ProductData::factory()->create([
        'product_id' => $product->id,
        $tenantFK => $siteId,
        'category_id' => $catA->id,
        'has_free_delivery' => false,
        'is_active' => true,
    ]);

    $updater = app(ProductBulkUpdater::class);

    // 1) No change: leave untouched
    $result = $updater->apply([
        // all toggles off => no updates
    ], [$product]);

    expect($result['successCount'])->toBe(0);
    $row = $product->productData()->where($tenantFK, $siteId)->first();
    expect($row->category_id)->toBe($catA->id);
    expect((bool) $row->has_free_delivery)->toBeFalse();

    // 2) Update(blank): clear the category, set free delivery true
    $result = $updater->apply([
        'update_categories' => true,
        'category_id' => null,
        'update_free_delivery' => true,
        'free_delivery_value' => true,
    ], [$product]);

    expect($result['successCount'])->toBe(1);
    $row->refresh();
    expect($row->category_id)->toBeNull();
    expect((bool) $row->has_free_delivery)->toBeTrue();
});

it('groups add/remove are tenant-aware', function (): void {
    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key', 'site_id');
    $site = \Filament\Facades\Filament::getTenant();

    $otherSite = \Workbench\App\Models\Site::factory()->create();

    // Group for current tenant
    $gCurrent = Group::factory()->create([$tenantFK => $site->id, 'is_active' => true]);
    // Group for other tenant
    $gOther = Group::factory()->create([$tenantFK => $otherSite->id, 'is_active' => true]);

    $product = Product::factory()->create();

    $updater = app(ProductBulkUpdater::class);

    // Try to add other-tenant group: should be ignored (scoped out)
    $updater->apply([
        'update_groups' => true,
        'groups_add_ids' => [$gOther->id],
    ], [$product]);

    expect($product->groups()->count())->toBe(0);

    // Add current tenant group: should attach
    $updater->apply([
        'update_groups' => true,
        'groups_add_ids' => [$gCurrent->id],
    ], [$product]);

    expect($product->groups()->pluck('id')->all())->toContain($gCurrent->id);

    // Remove it again
    $updater->apply([
        'update_groups' => true,
        'groups_remove_ids' => [$gCurrent->id],
    ], [$product]);

    expect($product->groups()->count())->toBe(0);
});

it('inserts price correctly', function (): void {
    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key', 'site_id');
    $site = \Filament\Facades\Filament::getTenant();

    $priceList = PriceList::factory()->create();
    // Enable list for current tenant
    $priceList->priceListData()->create([$tenantFK => $site->id, 'is_active' => true]);

    $product = Product::factory()->create();

    $updater = app(ProductBulkUpdater::class);

    $today = now()->toDateString();
    $result = $updater->apply([
        'update_prices' => true,
        'price_list_id' => $priceList->id,
        'price' => '12.34',
        'valid_from' => $today,
        'valid_to' => null,
        'tax_included' => true,
    ], [$product]);

    expect($result['successCount'])->toBe(1);
    $price = \Eclipse\Catalogue\Models\Product\Price::query()
        ->where('product_id', $product->id)
        ->where('price_list_id', $priceList->id)
        ->whereDate('valid_from', $today)
        ->first();

    expect($price)->not()->toBeNull();
    expect((string) $price->price)->toBe('12.34');
    expect((bool) $price->tax_included)->toBeTrue();
});

it('rolls back only the failing product inside per-product transaction', function (): void {
    $p1 = Product::factory()->create();
    $p2 = Product::factory()->create();

    // Make saving p1 fail when changing product type
    \Eclipse\Catalogue\Models\Product::saving(function ($model) use ($p1) {
        if ($model->id === $p1->id && $model->isDirty('product_type_id')) {
            throw new Exception('Induced failure');
        }
    });

    $updater = app(ProductBulkUpdater::class);

    $result = $updater->apply([
        'update_product_type' => true,
        'product_type_id' => null, // force a change on both
    ], [$p1, $p2]);

    // One fails, one succeeds
    expect($result['successCount'])->toBe(1);
    expect($result['errors'])->not()->toBeEmpty();
});
