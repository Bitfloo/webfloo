<?php

namespace Webfloo\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Webfloo\Filament\Resources\MenuItemResource\Pages\CreateMenuItem;
use Webfloo\Filament\Resources\MenuItemResource\Pages\EditMenuItem;
use Webfloo\Filament\Resources\MenuItemResource\Pages\ListMenuItems;
use Webfloo\Models\MenuItem;
use Webfloo\Support\ModuleRegistry;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bars3;

    protected static ?int $navigationSort = 50;

    protected static ?string $recordTitleAttribute = 'label';

    public static function getNavigationGroup(): ?string
    {
        return __('System');
    }

    public static function getModelLabel(): string
    {
        return __('Element menu');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Menu');
    }

    public static function canAccess(): bool
    {
        if (! ModuleRegistry::isEnabled('menu')) {
            return false;
        }

        return auth()->user()?->can('view_any_menu_item') === true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Element menu'))
                ->columns(2)
                ->schema([
                    TextInput::make('label')
                        ->label(__('Etykieta'))
                        ->required()
                        ->maxLength(100),

                    TextInput::make('href')
                        ->label(__('Link (URL)'))
                        ->placeholder(__('#services lub /kontakt'))
                        ->maxLength(255),

                    Select::make('target')
                        ->label(__('Otworz w'))
                        ->options(MenuItem::getTargetOptions())
                        ->default('_self')
                        ->native(false),

                    Select::make('location')
                        ->label(__('Lokalizacja'))
                        ->options(MenuItem::getLocationOptions())
                        ->required()
                        ->native(false)
                        ->live(),

                    Select::make('parent_id')
                        ->label(__('Element nadrzedny'))
                        ->relationship(
                            name: 'parent',
                            titleAttribute: 'label',
                            modifyQueryUsing: fn (Get $get, Builder $query) => $query
                                ->where('location', $get('location'))
                                ->whereNull('parent_id')
                        )
                        ->searchable()
                        ->preload()
                        ->placeholder(__('Brak (poziom główny)'))
                        ->visible(fn (Get $get): bool => filled($get('location'))),

                    TextInput::make('sort_order')
                        ->label(__('Kolejność'))
                        ->numeric()
                        ->default(0)
                        ->minValue(0),

                    Toggle::make('is_active')
                        ->label(__('Aktywny'))
                        ->default(true)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['parent']))
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('50px'),

                TextColumn::make('label')
                    ->label(__('Etykieta'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('href')
                    ->label(__('Link'))
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('location')
                    ->label(__('Lokalizacja'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => MenuItem::getLocationOptions()[$state] ?? $state)
                    ->color('gray'),

                TextColumn::make('parent.label')
                    ->label(__('Nadrzedny'))
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label(__('Aktywny'))
                    ->boolean(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('location')
                    ->label(__('Lokalizacja'))
                    ->options(MenuItem::getLocationOptions()),

                TernaryFilter::make('is_active')
                    ->label(__('Aktywny')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                'location',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMenuItems::route('/'),
            'create' => CreateMenuItem::route('/create'),
            'edit' => EditMenuItem::route('/{record}/edit'),
        ];
    }
}
