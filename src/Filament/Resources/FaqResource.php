<?php

namespace Webfloo\Filament\Resources;

use BackedEnum;
use Webfloo\Filament\Resources\FaqResource\Pages\CreateFaq;
use Webfloo\Filament\Resources\FaqResource\Pages\EditFaq;
use Webfloo\Filament\Resources\FaqResource\Pages\ListFaqs;
use Webfloo\Models\Faq;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
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

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::QuestionMarkCircle;

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'question';

    public static function getNavigationGroup(): ?string
    {
        return __('Treści');
    }

    public static function getModelLabel(): string
    {
        return __('FAQ');
    }

    public static function getPluralModelLabel(): string
    {
        return __('FAQ');
    }

    public static function canAccess(): bool
    {
        if (! config('webfloo.features.faq', true)) {
            return false;
        }

        return auth()->user()?->can('view_any_faq') === true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Pytanie i odpowiedź')
                ->schema([
                    TextInput::make('question')
                        ->label('Pytanie')
                        ->required()
                        ->maxLength(500)
                        ->columnSpanFull(),

                    RichEditor::make('answer')
                        ->label('Odpowiedź')
                        ->required()
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'link',
                            'bulletList',
                            'orderedList',
                        ])
                        ->columnSpanFull(),

                    TextInput::make('icon')
                        ->label('Ikona (Tabler)')
                        ->placeholder('np. briefcase, credit-card, clock')
                        ->helperText('Nazwa ikony Tabler bez prefiksu. Zobacz: tabler.io/icons')
                        ->maxLength(100),

                    TextInput::make('category')
                        ->label('Kategoria')
                        ->maxLength(100)
                        ->datalist([
                            'Ogólne',
                            'Płatności',
                            'Współpraca',
                            'Techniczne',
                            'Realizacja',
                        ])
                        ->placeholder('Wybierz lub wpisz kategorię'),
                ]),

            Section::make('Ustawienia')
                ->columns(2)
                ->schema([
                    TextInput::make('sort_order')
                        ->label('Kolejność')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),

                    Toggle::make('is_active')
                        ->label('Aktywny')
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
                    ->width(50),

                TextColumn::make('question')
                    ->label('Pytanie')
                    ->searchable()
                    ->sortable()
                    ->limit(80)
                    ->wrap(),

                TextColumn::make('category')
                    ->label('Kategoria')
                    ->badge()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Aktywny')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Aktywny'),
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
            'index' => ListFaqs::route('/'),
            'create' => CreateFaq::route('/create'),
            'edit' => EditFaq::route('/{record}/edit'),
        ];
    }
}
