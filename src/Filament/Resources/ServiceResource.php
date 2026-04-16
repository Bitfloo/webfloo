<?php

namespace Webfloo\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Webfloo\Filament\Resources\ServiceResource\Pages\CreateService;
use Webfloo\Filament\Resources\ServiceResource\Pages\EditService;
use Webfloo\Filament\Resources\ServiceResource\Pages\ListServices;
use Webfloo\Models\Service;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Squares2x2;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string
    {
        return __('Treści');
    }

    public static function getModelLabel(): string
    {
        return __('Usługa');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Usługi');
    }

    public static function canAccess(): bool
    {
        if (! config('webfloo.features.services', true)) {
            return false;
        }

        return auth()->user()?->can('view_any_service') === true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informacje')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Tytuł')
                        ->required()
                        ->maxLength(100),

                    Select::make('icon')
                        ->label('Ikona')
                        ->options(Service::getIconOptions())
                        ->required()
                        ->native(false)
                        ->searchable(),

                    Textarea::make('description')
                        ->label('Opis')
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),

                    TextInput::make('href')
                        ->label('Link')
                        ->url()
                        ->maxLength(255)
                        ->placeholder('https://...'),
                ]),

            Section::make('Ustawienia')
                ->columns(3)
                ->schema([
                    TextInput::make('sort_order')
                        ->label('Kolejność')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),

                    Toggle::make('is_active')
                        ->label('Aktywna')
                        ->default(true),

                    Toggle::make('is_featured')
                        ->label('Wyróżniona')
                        ->helperText('Pokaż na stronie głównej')
                        ->default(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                TextColumn::make('title')
                    ->label('Tytuł')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('icon')
                    ->label('Ikona')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Service::getIconOptions()[$state] ?? $state),

                TextColumn::make('description')
                    ->label('Opis')
                    ->limit(50)
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Aktywna')
                    ->boolean(),

                IconColumn::make('is_featured')
                    ->label('Wyróżniona')
                    ->boolean()
                    ->toggleable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Aktywna'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
