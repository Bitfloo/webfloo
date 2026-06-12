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
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Webfloo\Filament\Resources\RedirectResource\Pages\CreateRedirect;
use Webfloo\Filament\Resources\RedirectResource\Pages\EditRedirect;
use Webfloo\Filament\Resources\RedirectResource\Pages\ListRedirects;
use Webfloo\Models\Redirect;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowsRightLeft;

    protected static ?int $navigationSort = 8;

    protected static ?string $recordTitleAttribute = 'from_path';

    public static function getNavigationGroup(): ?string
    {
        return __('Treści');
    }

    public static function getModelLabel(): string
    {
        return __('Przekierowanie');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Przekierowania');
    }

    public static function canAccess(): bool
    {
        if (! config('webfloo.features.redirects', false)) {
            return false;
        }

        return auth()->user()?->can(webfloo_permission('view_any', 'redirect')) === true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Przekierowanie'))
                ->columns(2)
                ->schema([
                    TextInput::make('from_path')
                        ->label(__('Ze ścieżki'))
                        ->placeholder('/stara-strona')
                        ->helperText(__('Działa tylko gdy ścieżka nie istnieje (404). Żywa strona zawsze wygrywa.'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->dehydrateStateUsing(fn (string $state): string => Redirect::normalizePath($state)),

                    TextInput::make('to_path')
                        ->label(__('Na ścieżkę'))
                        ->placeholder('/nowa-strona')
                        ->helperText(__('Tylko ścieżki w obrębie witryny (open-redirect guard).'))
                        ->required()
                        ->maxLength(255)
                        ->rules(['regex:/^\//']),

                    Select::make('status_code')
                        ->label(__('Kod HTTP'))
                        ->options([
                            301 => __('301 — trwałe'),
                            302 => __('302 — tymczasowe'),
                        ])
                        ->default(301)
                        ->native(false),

                    Toggle::make('is_active')
                        ->label(__('Aktywne'))
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('from_path')
                    ->label(__('Ze ścieżki'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('to_path')
                    ->label(__('Na ścieżkę'))
                    ->searchable(),

                TextColumn::make('status_code')
                    ->label(__('Kod'))
                    ->badge()
                    ->color(fn (int $state): string => $state === 301 ? 'success' : 'warning'),

                TextColumn::make('hits_count')
                    ->label(__('Trafienia'))
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('Aktywne'))
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label(__('Utworzono'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Aktywne')),
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
            'index' => ListRedirects::route('/'),
            'create' => CreateRedirect::route('/create'),
            'edit' => EditRedirect::route('/{record}/edit'),
        ];
    }
}
