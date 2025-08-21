<?php

namespace Eclipse\Catalogue\Filament\Resources;

use Eclipse\Catalogue\Filament\Resources\PropertyValueResource\Pages;
use Eclipse\Catalogue\Models\Property;
use Eclipse\Catalogue\Models\PropertyValue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PropertyValueResource extends Resource
{
    use Translatable;

    protected static ?string $model = PropertyValue::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Catalogue';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Value Information')
                    ->schema([
                        Forms\Components\Select::make('property_id')
                            ->label('Property')
                            ->relationship('property', 'name')
                            ->required()
                            ->disabled(fn ($livewire) => $livewire instanceof Pages\CreatePropertyValue && request()->has('property')),

                        Forms\Components\TextInput::make('value')
                            ->label('Value')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('info_url')
                            ->label('Info URL')
                            ->helperText('Optional "read more" link')
                            ->url()
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('image')
                            ->label('Image')
                            ->helperText('Optional image for this value (e.g., brand logo)')
                            ->image()
                            ->formatStateUsing(function ($state) {
                                if (is_string($state) || $state === null) {
                                    return $state;
                                }

                                if (is_array($state)) {
                                    $locale = app()->getLocale();
                                    $byLocale = $state[$locale] ?? null;
                                    if (is_string($byLocale) && $byLocale !== '') {
                                        return $byLocale;
                                    }

                                    foreach ($state as $value) {
                                        if (is_string($value) && $value !== '') {
                                            return $value;
                                        }
                                    }

                                    return null;
                                }

                                return null;
                            })
                            ->nullable()
                            ->disk('public')
                            ->directory('property-values'),

                        Forms\Components\TextInput::make('sort')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $propertyId = request()->has('property') ? (int) request('property') : null;
        $property = $propertyId ? Property::find($propertyId) : null;

        $table = $table
            ->columns([
                Tables\Columns\TextColumn::make('property.name')
                    ->label('Property')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->size(40),

                Tables\Columns\TextColumn::make('info_url')
                    ->label('Info URL')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('sort')
                    ->label('Sort')
                    ->sortable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('property')
                    ->relationship('property', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);

        if ($property && $property->enable_sorting) {
            $table = $table->reorderable('sort')->defaultSort('sort');
        } else {
            $table = $table->defaultSort('value');
        }

        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (request()->has('property')) {
                    $query->where('property_id', request('property'));
                }
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPropertyValues::route('/'),
            'create' => Pages\CreatePropertyValue::route('/create'),
            'edit' => Pages\EditPropertyValue::route('/{record}/edit'),
        ];
    }

    /**
     * Attributes stored as JSON translations on the model.
     */
    public static function getTranslatableAttributes(): array
    {
        return [
            'value',
            'info_url',
            'image',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (request()->has('property')) {
            $query->where('property_id', request('property'));
        }

        return $query;
    }
}
