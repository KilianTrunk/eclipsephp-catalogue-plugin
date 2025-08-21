<?php

namespace Eclipse\Catalogue\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Eclipse\Catalogue\Filament\Resources\PropertyResource\Pages;
use Eclipse\Catalogue\Filament\Resources\PropertyResource\RelationManagers;
use Eclipse\Catalogue\Models\ProductType;
use Eclipse\Catalogue\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PropertyResource extends Resource implements HasShieldPermissions
{
    use Translatable;

    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Catalogue';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('code')
                            ->label('Code')
                            ->helperText('Optional alphanumeric code with underscores, automatically converted to lowercase')
                            ->regex('/^[a-zA-Z0-9_]*$/')
                            ->unique(ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3),

                        Forms\Components\TextInput::make('internal_name')
                            ->label('Internal Name')
                            ->helperText('Internal name for distinction, not translatable')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Forms\Components\Toggle::make('is_global')
                            ->label('Global Property')
                            ->helperText('Auto-assigned to all product types')
                            ->reactive(),

                        Forms\Components\Select::make('max_values')
                            ->label('Maximum Values')
                            ->options([
                                1 => 'Single value (1)',
                                2 => 'Multiple values (2+)',
                            ])
                            ->helperText('Controls form field type: single = radio/select, multiple = checkbox/multiselect'),

                        Forms\Components\Toggle::make('enable_sorting')
                            ->label('Enable Manual Sorting')
                            ->helperText('Allow drag-and-drop sorting of property values'),

                        Forms\Components\Toggle::make('is_filter')
                            ->label('Show as Filter')
                            ->helperText('Display property as filter in product table'),
                    ])->columns(2),

                Forms\Components\Section::make('Product Types')
                    ->schema([
                        Forms\Components\CheckboxList::make('product_types')
                            ->label('Assign to Product Types')
                            ->relationship('productTypes', 'name')
                            ->options(ProductType::pluck('name', 'id'))
                            ->helperText('Select product types for this property (ignored if Global is enabled)')
                            ->hidden(fn (Forms\Get $get) => $get('is_global')),
                    ])
                    ->hidden(fn (Forms\Get $get) => $get('is_global')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->relationship(function () {
                $state = $this->getLivewire()->getTableFilterState('product_type') ?? [];

                $selected = [];
                if (is_array($state)) {
                    if (array_key_exists('values', $state) && is_array($state['values'])) {
                        $selected = $state['values'];
                    } elseif (array_key_exists('value', $state)) {
                        $selected = is_array($state['value']) ? $state['value'] : [$state['value']];
                    } else {
                        $selected = $state;
                    }
                }

                $selected = array_values(array_filter($selected, fn ($v) => is_numeric($v)));

                if (count($selected) === 1) {
                    $type = ProductType::find((int) $selected[0]);

                    return $type?->properties();
                }

                return null;
            })
            ->query(fn () => Property::query())
            ->reorderable(
                column: 'pivot.sort',
                condition: function (): bool {
                    $state = $this->getLivewire()->getTableFilterState('product_type') ?? [];
                    $selected = [];
                    if (is_array($state)) {
                        if (array_key_exists('values', $state) && is_array($state['values'])) {
                            $selected = $state['values'];
                        } elseif (array_key_exists('value', $state)) {
                            $selected = is_array($state['value']) ? $state['value'] : [$state['value']];
                        } else {
                            $selected = $state;
                        }
                    }
                    $selected = array_values(array_filter($selected, fn ($v) => is_numeric($v)));

                    return count($selected) === 1;
                }
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('internal_name')
                    ->label('Internal Name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_global')
                    ->label('Global')
                    ->boolean(),

                Tables\Columns\TextColumn::make('max_values')
                    ->label('Max Values')
                    ->formatStateUsing(fn ($state) => $state === 1 ? 'Single' : 'Multiple'),

                Tables\Columns\IconColumn::make('enable_sorting')
                    ->label('Sortable')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_filter')
                    ->label('Filter')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('values_count')
                    ->label('Values')
                    ->counts('values'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_type')
                    ->label('Product Type')
                    ->relationship('productTypes', 'name')
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_global')
                    ->label('Global Properties'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Properties'),

                Tables\Filters\TernaryFilter::make('is_filter')
                    ->label('Filter Properties'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('values')
                        ->label('Values')
                        ->icon('heroicon-o-list-bullet')
                        ->url(fn (Property $record): string => PropertyValueResource::getUrl('index', ['property' => $record->id])),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->label('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn (Property $record): string => PropertyValueResource::getUrl('index', ['property' => $record->id]))
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ValuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'restore',
            'restore_any',
        ];
    }
}
