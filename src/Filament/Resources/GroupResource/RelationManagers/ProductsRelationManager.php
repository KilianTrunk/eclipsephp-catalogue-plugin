<?php

namespace Eclipse\Catalogue\Filament\Resources\GroupResource\RelationManagers;

use Eclipse\Catalogue\Filament\Resources\ProductResource;
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
                    ->modalHeading('Add Product')
                    ->modalSubmitActionLabel('Add Product')
                    ->modalCancelActionLabel('Cancel')
                    ->extraModalFooterActions(
                        fn (Tables\Actions\AttachAction $action): array => [
                            $action->makeModalSubmitAction('submitAnother', ['another' => true])
                                ->label('Add Product & Add Another'),
                        ]
                    )
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()->searchable(),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('edit_product')
                    ->label('Edit Product')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record): string => ProductResource::getUrl('edit', ['record' => $record->id]))
                    ->openUrlInNewTab(),
                Tables\Actions\DetachAction::make()->label('Remove'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()->label('Remove'),
                ]),
            ]);
    }
}
