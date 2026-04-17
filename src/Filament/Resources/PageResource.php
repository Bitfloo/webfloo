<?php

namespace Webfloo\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Webfloo\Filament\Resources\PageResource\Pages\CreatePage;
use Webfloo\Filament\Resources\PageResource\Pages\EditPage;
use Webfloo\Filament\Resources\PageResource\Pages\ListPages;
use Webfloo\Models\Page;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string
    {
        return __('Strony');
    }

    public static function getNavigationLabel(): string
    {
        return __('Podstrony CMS');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Podstrony');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_any_page') === true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Page')
                ->columnSpanFull()
                ->tabs([
                    Tab::make(__('Treść'))
                        ->icon(Heroicon::DocumentText)
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('title')
                                        ->label(__('Tytuł'))
                                        ->required()
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (string $operation, mixed $state, callable $set) {
                                            if ($operation === 'create' && is_string($state)) {
                                                $set('slug', Str::slug($state));
                                            }
                                        }),

                                    TextInput::make('slug')
                                        ->label(__('Slug'))
                                        ->required()
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true)
                                        ->rules(['alpha_dash']),
                                ]),

                            RichEditor::make('content')
                                ->label(__('Treść'))
                                ->columnSpanFull()
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory('pages')
                                ->toolbarButtons([
                                    'blockquote',
                                    'bold',
                                    'bulletList',
                                    'codeBlock',
                                    'h2',
                                    'h3',
                                    'italic',
                                    'link',
                                    'orderedList',
                                    'redo',
                                    'strike',
                                    'underline',
                                    'undo',
                                ]),

                            Select::make('parent_id')
                                ->label(__('Strona nadrzędna'))
                                ->relationship('parent', 'title')
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->placeholder(__('Brak (strona główna)')),
                        ]),

                    Tab::make(__('SEO'))
                        ->icon(Heroicon::MagnifyingGlass)
                        ->schema([
                            TextInput::make('meta_title')
                                ->label(__('Meta tytuł'))
                                ->maxLength(70)
                                ->placeholder(__('Pozostaw puste, aby użyć tytułu strony'))
                                ->helperText(__('Zalecane: 50-60 znaków')),

                            Textarea::make('meta_description')
                                ->label(__('Meta opis'))
                                ->maxLength(160)
                                ->rows(3)
                                ->helperText(fn (mixed $state): string => __('Znaki').': '.strlen(is_string($state) ? $state : '').'/160')
                                ->placeholder(__('Krótki opis dla wyszukiwarek')),

                            FileUpload::make('meta_image')
                                ->label(__('Obraz udostępniania'))
                                ->image()
                                ->disk('public')
                                ->directory('pages/meta')
                                ->imageResizeMode('cover')
                                ->imageCropAspectRatio('1200:630')
                                ->imageResizeTargetWidth('1200')
                                ->imageResizeTargetHeight('630')
                                ->maxSize(2048)
                                ->helperText(__('Zalecany rozmiar: 1200x630 pikseli')),
                        ]),

                    Tab::make(__('Ustawienia'))
                        ->icon(Heroicon::Cog6Tooth)
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('template')
                                        ->label(__('Szablon'))
                                        ->options([
                                            'default' => 'Default',
                                            'home' => 'Home',
                                            'contact' => 'Contact',
                                            'services' => 'Services',
                                            'about' => 'About',
                                        ])
                                        ->default('default')
                                        ->native(false)
                                        ->required(),

                                    Select::make('status')
                                        ->label(__('Status'))
                                        ->options([
                                            'draft' => 'Draft',
                                            'published' => 'Published',
                                            'archived' => 'Archived',
                                        ])
                                        ->default('draft')
                                        ->native(false)
                                        ->required(),

                                    DateTimePicker::make('published_at')
                                        ->label(__('Data publikacji'))
                                        ->nullable()
                                        ->displayFormat('d.m.Y H:i')
                                        ->helperText(__('Zaplanuj publikację na przyszłą datę')),

                                    TextInput::make('sort_order')
                                        ->label(__('Kolejność'))
                                        ->numeric()
                                        ->default(0)
                                        ->helperText(__('Niższe wartości wyświetlane pierwsze')),
                                ]),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with([Page::parentChainEagerLoad()]))
            ->columns([
                TextColumn::make('title')
                    ->label(__('Tytuł'))
                    ->searchable()
                    ->sortable()
                    ->description(fn (Page $record): string => $record->slug),

                TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('template')
                    ->label(__('Szablon'))
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'published' => 'success',
                        'archived' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('parent.title')
                    ->label(__('Nadrzędna'))
                    ->placeholder(__('Główna'))
                    ->toggleable(),

                TextColumn::make('published_at')
                    ->label(__('Opublikowano'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder(__('Nie zaplanowano')),

                TextColumn::make('sort_order')
                    ->label(__('Kolejność'))
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('updated_at')
                    ->label(__('Zaktualizowano'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),

                SelectFilter::make('template')
                    ->label(__('Szablon'))
                    ->options([
                        'default' => 'Default',
                        'home' => 'Home',
                        'contact' => 'Contact',
                        'services' => 'Services',
                        'about' => 'About',
                    ]),

                SelectFilter::make('parent_id')
                    ->label(__('Strona nadrzędna'))
                    ->relationship('parent', 'title')
                    ->searchable()
                    ->preload()
                    ->placeholder(__('Wszystkie strony')),
            ])
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make()
                    ->excludeAttributes(['slug', 'published_at'])
                    ->beforeReplicaSaved(function (Page $replica): void {
                        $replica->slug = $replica->slug.'-copy-'.now()->timestamp;
                        $replica->status = 'draft';
                        $replica->title = $replica->title.' (Copy)';
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    BulkAction::make('publish')
                        ->label(__('Publish'))
                        ->icon(Heroicon::Check)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(function (Page $record) {
                            $record->update([
                                'status' => 'published',
                                'published_at' => $record->published_at ?? now(),
                            ]);
                        }))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('unpublish')
                        ->label(__('Unpublish'))
                        ->icon(Heroicon::XMark)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(function (Page $record) {
                            $record->update(['status' => 'draft']);
                        }))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'draft')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
