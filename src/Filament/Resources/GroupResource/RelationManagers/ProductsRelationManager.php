<?php

namespace Eclipse\Catalogue\Filament\Resources\GroupResource\RelationManagers;

use Eclipse\Catalogue\Filament\Resources\ProductResource;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('code')
                    ->label('Code')
                    ->copyable()
                    ->toggleable(),
            ])
            ->defaultSort('pim_group_has_product.sort')
            ->reorderable('pim_group_has_product.sort')
            ->reorderRecordsTriggerAction(
                fn (Tables\Actions\Action $action, bool $isReordering) => $action
                    ->button()
                    ->label($isReordering ? 'Disable reordering' : 'Enable reordering')
                    ->icon($isReordering ? 'heroicon-o-x-mark' : 'heroicon-o-arrows-up-down')
                    ->color($isReordering ? 'danger' : 'primary')
            )
            ->paginated(false)
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Add product')
                    ->modalHeading('Add Product to Group')
                    ->modalSubmitActionLabel('Add Product')
                    ->modalCancelActionLabel('Cancel')
                    ->extraModalFooterActions(
                        fn (Tables\Actions\AttachAction $action): array => [
                            $action->makeModalSubmitAction('submitAnother', ['another' => true])
                                ->label('Add & Add Another'),
                        ]
                    )
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Select product')
                            ->searchable(),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('edit_product')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record): string => ProductResource::getUrl('edit', ['record' => $record->id]))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('move_product')
                    ->label('Reorder')
                    ->icon('heroicon-o-arrows-up-down')
                    ->modalHeading(fn ($record) => 'Reorder: '.$record->name)
                    ->modalSubmitActionLabel('Move Product')
                    ->modalCancelActionLabel('Cancel')
                    ->form(function ($record, $livewire) {
                        return [
                            Forms\Components\Placeholder::make('moving_info')
                                ->label('You are moving')
                                ->content($record->name),

                            Forms\Components\Select::make('reference_id')
                                ->label('Place relative to')
                                ->options(
                                    $livewire->getOwnerRecord()
                                        ->products()
                                        ->pluck('name', 'id')
                                        ->except($record->id)
                                )
                                ->searchable()
                                ->reactive()
                                ->required(),

                            Forms\Components\Radio::make('position')
                                ->label('Position')
                                ->options([
                                    'before' => 'Before selected product',
                                    'after' => 'After selected product',
                                ])
                                ->default('before')
                                ->inline()
                                ->reactive(),

                            Forms\Components\Placeholder::make('preview')
                                ->label('Result')
                                ->content(function (callable $get, $livewire) use ($record) {
                                    $refId = $get('reference_id');
                                    $position = $get('position') ?? 'before';

                                    if (! $refId) {
                                        return 'Select a product to see the result.';
                                    }

                                    $refName = $livewire->getOwnerRecord()
                                        ->products()
                                        ->where('catalogue_products.id', $refId)
                                        ->value('name');

                                    if (! $refName) {
                                        return 'Select a valid product.';
                                    }

                                    return sprintf(
                                        '%s will be moved %s %s',
                                        $record->name,
                                        $position,
                                        $refName
                                    );
                                }),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        $group = $this->getOwnerRecord();

                        $reference = $group->products()
                            ->where('catalogue_products.id', $data['reference_id'])
                            ->firstOrFail();

                        if ($data['position'] === 'before') {
                            $previous = $group->products()
                                ->wherePivot('sort', '<', $reference->pivot->sort)
                                ->orderByDesc('pim_group_has_product.sort')
                                ->first();

                            $newSort = $previous
                                ? intdiv($previous->pivot->sort + $reference->pivot->sort, 2)
                                : $reference->pivot->sort - 1000;
                        } else {
                            $next = $group->products()
                                ->wherePivot('sort', '>', $reference->pivot->sort)
                                ->orderBy('pim_group_has_product.sort')
                                ->first();

                            $newSort = $next
                                ? intdiv($reference->pivot->sort + $next->pivot->sort, 2)
                                : $reference->pivot->sort + 1000;
                        }

                        $group->products()->updateExistingPivot($record->id, [
                            'sort' => $newSort,
                        ]);
                    }),

                Tables\Actions\DetachAction::make()
                    ->label('Remove'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()->label('Remove'),
                ]),
            ]);
    }
}
