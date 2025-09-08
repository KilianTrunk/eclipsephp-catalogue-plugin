<?php

namespace Eclipse\Catalogue\Filament\Tables\Actions;

use Eclipse\Catalogue\Models\Category;
use Eclipse\Catalogue\Models\Group;
use Eclipse\Catalogue\Models\ProductType;
use Eclipse\Catalogue\Services\ProductBulkUpdater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Facades\App;

class BulkUpdateProductsAction extends BulkAction
{
    /**
     * Make the bulk update action.
     *
     * @param  string|null  $name  The name of the action.
     * @return static The bulk update action.
     */
    public static function make(?string $name = null): static
    {
        $action = parent::make($name ?? 'bulk_update')
            ->label('Bulk Update')
            ->icon('heroicon-o-wrench')
            ->deselectRecordsAfterCompletion()
            ->form([
                Section::make('Product Type')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Toggle::make('update_product_type')
                            ->label('Update product type')
                            ->helperText('Select a product type to assign to selected products.'),
                        Select::make('product_type_id')
                            ->label('Product Type')
                            ->options(function () {
                                $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
                                $currentTenant = \Filament\Facades\Filament::getTenant();

                                $query = ProductType::query();

                                if ($tenantFK && $currentTenant) {
                                    $query->whereHas('productTypeData', function ($q) use ($tenantFK, $currentTenant) {
                                        $q->where($tenantFK, $currentTenant->id)
                                            ->where('is_active', true);
                                    });
                                } else {
                                    $query->whereHas('productTypeData', function ($q) {
                                        $q->where('is_active', true);
                                    });
                                }

                                return $query->pluck('name', 'id')->toArray();
                            })
                            ->searchable()
                            ->nullable(),
                    ]),
                Section::make('Free delivery')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Toggle::make('update_free_delivery')
                            ->label('Update free delivery')
                            ->helperText('Set the free delivery flag for the current tenant.'),
                        Toggle::make('free_delivery_value')
                            ->label('Has free delivery'),
                    ]),
                Section::make('Categories')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Toggle::make('update_categories')
                            ->label('Update categories')
                            ->helperText('Choose a category to assign to selected products for the current tenant.'),
                        Select::make('category_id')
                            ->label('Category')
                            ->options(function () {
                                $tenantFK = config('eclipse-catalogue.tenancy.foreign_key', 'site_id');
                                $currentTenant = \Filament\Facades\Filament::getTenant();
                                $query = Category::query()->withoutGlobalScopes();
                                if ($tenantFK && $currentTenant) {
                                    $query->where($tenantFK, $currentTenant->id);
                                }

                                return $query->orderBy('name')->pluck('name', 'id')->toArray();
                            })
                            ->searchable()
                            ->nullable(),
                    ]),
                Section::make('Groups')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Toggle::make('update_groups')
                            ->label('Update groups')
                            ->helperText('Enable to add or remove selected products to/from groups.'),
                        Select::make('groups_add_ids')
                            ->label('Add to groups')
                            ->multiple()
                            ->options(fn () => Group::query()->active()->forCurrentTenant()->pluck('name', 'id')->toArray())
                            ->searchable(),
                        Select::make('groups_remove_ids')
                            ->label('Remove from groups')
                            ->multiple()
                            ->options(fn () => Group::query()->active()->forCurrentTenant()->pluck('name', 'id')->toArray())
                            ->searchable(),
                    ]),
                Section::make('Images')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Toggle::make('update_images')
                            ->label('Update images')
                            ->helperText('You can add new product images here. Any image that already exists on the specified position will be replaced.'),
                        FileUpload::make('cover_image')
                            ->label('Cover image')
                            ->image()
                            ->imageEditor(false)
                            ->directory('temp-images')
                            ->visibility('public')
                            ->storeFiles(true)
                            ->preserveFilenames()
                            ->nullable(),
                        FileUpload::make('image_1')
                            ->label('Image #1')
                            ->image()
                            ->imageEditor(false)
                            ->directory('temp-images')
                            ->visibility('public')
                            ->storeFiles(true)
                            ->preserveFilenames()
                            ->nullable(),
                        FileUpload::make('image_2')
                            ->label('Image #2')
                            ->image()
                            ->imageEditor(false)
                            ->directory('temp-images')
                            ->visibility('public')
                            ->storeFiles(true)
                            ->preserveFilenames()
                            ->nullable(),
                        FileUpload::make('image_3')
                            ->label('Image #3')
                            ->image()
                            ->imageEditor(false)
                            ->directory('temp-images')
                            ->visibility('public')
                            ->storeFiles(true)
                            ->preserveFilenames()
                            ->nullable(),
                    ]),
            ])
            ->action(function (array $data, $records) {
                /** @var ProductBulkUpdater $updater */
                $updater = App::make(ProductBulkUpdater::class);
                $result = $updater->apply($data, $records);

                $notification = Notification::make();
                if (($result['successCount'] ?? 0) > 0) {
                    $title = $result['successCount'] === 1
                        ? 'Updated 1 product'
                        : "Updated {$result['successCount']} products";
                    $notification->title($title)->success();
                } else {
                    $notification->title('No changes applied')->warning();
                }

                if (! empty($result['errors'] ?? [])) {
                    $notification->body('Some updates failed: '.implode(' | ', array_unique($result['errors'])));
                }

                $notification->send();
            });

        return $action;
    }
}
