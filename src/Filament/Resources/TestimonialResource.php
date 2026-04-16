<?php

namespace Webfloo\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Webfloo\Filament\Resources\TestimonialResource\Pages\CreateTestimonial;
use Webfloo\Filament\Resources\TestimonialResource\Pages\EditTestimonial;
use Webfloo\Filament\Resources\TestimonialResource\Pages\ListTestimonials;
use Webfloo\Models\Testimonial;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChatBubbleLeftRight;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'author';

    public static function getNavigationGroup(): ?string
    {
        return __('Treści');
    }

    public static function getModelLabel(): string
    {
        return __('Opinia');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Opinie');
    }

    public static function canAccess(): bool
    {
        if (! config('webfloo.features.testimonials', true)) {
            return false;
        }

        return auth()->user()?->can('view_any_testimonial') === true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Opinia')
                ->columns(2)
                ->schema([
                    Textarea::make('content')
                        ->label('Tresc opinii')
                        ->required()
                        ->rows(4)
                        ->maxLength(1000)
                        ->columnSpanFull(),

                    Select::make('rating')
                        ->label('Ocena')
                        ->options([
                            5 => str_repeat("\u{2B50}", 5).' (5)',
                            4 => str_repeat("\u{2B50}", 4).' (4)',
                            3 => str_repeat("\u{2B50}", 3).' (3)',
                            2 => str_repeat("\u{2B50}", 2).' (2)',
                            1 => str_repeat("\u{2B50}", 1).' (1)',
                        ])
                        ->default(5)
                        ->native(false),
                ]),

            Section::make('Autor')
                ->columns(2)
                ->schema([
                    TextInput::make('author')
                        ->label('Imie i nazwisko')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('role')
                        ->label('Stanowisko')
                        ->maxLength(100)
                        ->placeholder('np. CEO, CTO, Manager'),

                    TextInput::make('company')
                        ->label('Firma')
                        ->maxLength(100),

                    FileUpload::make('avatar')
                        ->label('Zdjęcie')
                        ->image()
                        ->avatar()
                        ->directory('testimonials')
                        ->visibility('public')
                        ->maxSize(1024)
                        ->imageEditor()
                        ->circleCropper(),
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

                ImageColumn::make('avatar')
                    ->label('Zdjęcie')
                    ->circular()
                    ->size(40),

                TextColumn::make('author')
                    ->label('Autor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company')
                    ->label('Firma')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('content')
                    ->label('Opinia')
                    ->limit(60)
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('rating')
                    ->label('Ocena')
                    ->formatStateUsing(fn (?int $state): string => $state ? str_repeat("\u{2B50}", $state) : '-')
                    ->alignCenter(),

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
            'index' => ListTestimonials::route('/'),
            'create' => CreateTestimonial::route('/create'),
            'edit' => EditTestimonial::route('/{record}/edit'),
        ];
    }
}
