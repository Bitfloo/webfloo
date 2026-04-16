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
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Webfloo\Filament\Resources\PostCategoryResource\Pages\CreatePostCategory;
use Webfloo\Filament\Resources\PostCategoryResource\Pages\EditPostCategory;
use Webfloo\Filament\Resources\PostCategoryResource\Pages\ListPostCategories;
use Webfloo\Models\PostCategory;

class PostCategoryResource extends Resource
{
    protected static ?string $model = PostCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::FolderOpen;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('Treści');
    }

    public static function getModelLabel(): string
    {
        return __('Kategoria');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Kategorie');
    }

    public static function canAccess(): bool
    {
        if (! config('webfloo.features.blog', true)) {
            return false;
        }

        return auth()->user()?->can('view_any_post_category') === true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nazwa')
                        ->required()
                        ->maxLength(100)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, mixed $state, callable $set) {
                            if ($operation === 'create' && is_string($state)) {
                                $set('slug', Str::slug($state));
                            }
                        }),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(100)
                        ->unique(ignoreRecord: true)
                        ->rules(['alpha_dash']),

                    TextInput::make('icon')
                        ->label('Ikona')
                        ->maxLength(50)
                        ->placeholder('np. tabler--code')
                        ->helperText('Nazwa ikony Iconify'),

                    Select::make('color')
                        ->label('Kolor')
                        ->options(PostCategory::getColorOptions())
                        ->default('primary')
                        ->native(false)
                        ->required(),

                    Textarea::make('description')
                        ->label('Opis')
                        ->maxLength(500)
                        ->rows(3)
                        ->columnSpanFull(),

                    TextInput::make('sort_order')
                        ->label('Kolejność')
                        ->numeric()
                        ->default(0),

                    Toggle::make('is_active')
                        ->label('Aktywna')
                        ->default(true),
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
                    ->alignCenter()
                    ->width(50),

                TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable()
                    ->sortable()
                    ->description(fn (PostCategory $record): string => $record->slug),

                TextColumn::make('color')
                    ->label('Kolor')
                    ->badge()
                    ->color(fn (string $state): string => $state)
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('posts_count')
                    ->label('Posty')
                    ->counts('posts')
                    ->sortable()
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Aktywna')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('updated_at')
                    ->label('Zaktualizowano')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostCategories::route('/'),
            'create' => CreatePostCategory::route('/create'),
            'edit' => EditPostCategory::route('/{record}/edit'),
        ];
    }
}
