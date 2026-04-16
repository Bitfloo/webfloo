<?php

namespace Webfloo\Filament\Resources;

use BackedEnum;
use Webfloo\Filament\Resources\PostResource\Pages\CreatePost;
use Webfloo\Filament\Resources\PostResource\Pages\EditPost;
use Webfloo\Filament\Resources\PostResource\Pages\ListPosts;
use Webfloo\Models\Post;
use Webfloo\Models\Project;
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
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Newspaper;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string
    {
        return __('Treści');
    }

    public static function getModelLabel(): string
    {
        return __('Post');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Posty');
    }

    public static function canAccess(): bool
    {
        if (! config('webfloo.features.blog', true)) {
            return false;
        }

        return auth()->user()?->can('view_any_post') === true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Post')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Podstawowe')
                        ->icon(Heroicon::DocumentText)
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('title')
                                        ->label('Tytuł')
                                        ->required()
                                        ->maxLength(200)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (string $operation, mixed $state, callable $set) {
                                            if ($operation === 'create' && is_string($state)) {
                                                $set('slug', Str::slug($state));
                                            }
                                        }),

                                    TextInput::make('slug')
                                        ->label('Slug')
                                        ->required()
                                        ->maxLength(200)
                                        ->unique(ignoreRecord: true)
                                        ->rules(['alpha_dash']),

                                    Select::make('post_category_id')
                                        ->label('Kategoria')
                                        ->relationship('category', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->required()
                                                ->maxLength(100),
                                            TextInput::make('slug')
                                                ->required()
                                                ->maxLength(100),
                                        ])
                                        ->nullable(),

                                    Select::make('author_id')
                                        ->label('Autor')
                                        ->relationship('author', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->nullable(),
                                ]),

                            Textarea::make('excerpt')
                                ->label('Zajawka')
                                ->maxLength(500)
                                ->rows(3)
                                ->helperText('Krótki opis widoczny na listingu i w meta description')
                                ->columnSpanFull(),

                            FileUpload::make('featured_image')
                                ->label('Obrazek wyróżniający')
                                ->image()
                                ->disk('public')
                                ->directory('posts')
                                ->imageResizeMode('cover')
                                ->imageCropAspectRatio('16:9')
                                ->imageResizeTargetWidth('1200')
                                ->imageResizeTargetHeight('675')
                                ->maxSize(2048)
                                ->columnSpanFull(),
                        ]),

                    Tab::make('Treść')
                        ->icon(Heroicon::PencilSquare)
                        ->schema([
                            RichEditor::make('content')
                                ->label('Treść artykułu')
                                ->columnSpanFull()
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory('posts/attachments')
                                ->toolbarButtons([
                                    'attachFiles',
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
                        ]),

                    Tab::make('SEO')
                        ->icon(Heroicon::MagnifyingGlass)
                        ->schema([
                            TextInput::make('meta_title')
                                ->label('Meta Title')
                                ->maxLength(70)
                                ->placeholder('Pozostaw puste, aby użyć tytułu artykułu')
                                ->helperText('Rekomendowane: 50-60 znaków'),

                            Textarea::make('meta_description')
                                ->label('Meta Description')
                                ->maxLength(160)
                                ->rows(3)
                                ->helperText(fn (mixed $state): string => 'Znaki: '.strlen(is_string($state) ? $state : '').'/160')
                                ->placeholder('Krótki opis dla wyszukiwarek'),

                            FileUpload::make('meta_image')
                                ->label('Obrazek OG')
                                ->image()
                                ->disk('public')
                                ->directory('posts/meta')
                                ->imageResizeMode('cover')
                                ->imageCropAspectRatio('1200:630')
                                ->imageResizeTargetWidth('1200')
                                ->imageResizeTargetHeight('630')
                                ->maxSize(2048)
                                ->helperText('Rekomendowany rozmiar: 1200x630 px'),

                            Toggle::make('no_index')
                                ->label('Nie indeksuj (noindex)')
                                ->helperText('Ukryj stronę przed wyszukiwarkami'),
                        ]),

                    Tab::make('Ustawienia')
                        ->icon(Heroicon::Cog6Tooth)
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('status')
                                        ->label('Status')
                                        ->options(Post::getStatusOptions())
                                        ->default('draft')
                                        ->native(false)
                                        ->required(),

                                    DateTimePicker::make('published_at')
                                        ->label('Data publikacji')
                                        ->nullable()
                                        ->displayFormat('d.m.Y H:i')
                                        ->helperText('Zaplanuj publikację na przyszłą datę'),

                                    Toggle::make('is_featured')
                                        ->label('Wyróżniony')
                                        ->helperText('Pokaż na głównej sekcji bloga'),

                                    TextInput::make('reading_time')
                                        ->label('Czas czytania (min)')
                                        ->numeric()
                                        ->placeholder('Automatycznie')
                                        ->helperText('Pozostaw puste dla automatycznego obliczenia'),

                                    TextInput::make('sort_order')
                                        ->label('Kolejność')
                                        ->numeric()
                                        ->default(0),

                                    TextInput::make('views_count')
                                        ->label('Wyświetlenia')
                                        ->numeric()
                                        ->default(0)
                                        ->disabled(),
                                ]),
                        ]),

                    Tab::make('Powiązania')
                        ->icon(Heroicon::Link)
                        ->schema([
                            Section::make('Powiązane projekty (Case Studies)')
                                ->description('Wybierz projekty powiązane z tym artykułem')
                                ->schema([
                                    Select::make('relatedProjects')
                                        ->label('Projekty')
                                        ->relationship('relatedProjects', 'title')
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->options(
                                            Project::query()
                                                ->where('is_active', true)
                                                ->pluck('title', 'id')
                                        ),
                                ]),

                            Section::make('Powiązane artykuły')
                                ->description('Wybierz artykuły wyświetlane jako "Czytaj więcej"')
                                ->schema([
                                    Select::make('relatedPosts')
                                        ->label('Artykuły')
                                        ->relationship('relatedPosts', 'title')
                                        ->multiple()
                                        ->searchable()
                                        ->preload(),
                                ]),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['category', 'author']))
            ->columns([
                ImageColumn::make('featured_image')
                    ->label('')
                    ->width(80)
                    ->height(45)
                    ->defaultImageUrl(url('/images/placeholder.jpg')),

                TextColumn::make('title')
                    ->label('Tytuł')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->description(fn (Post $record): string => Str::limit($record->excerpt ?? '', 60)),

                TextColumn::make('category.name')
                    ->label('Kategoria')
                    ->badge()
                    ->color(fn (Post $record): string => $record->category !== null ? $record->category->color : 'gray')
                    ->placeholder('Brak'),

                TextColumn::make('author.name')
                    ->label('Autor')
                    ->placeholder('Brak')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'published' => 'success',
                        'archived' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Szkic',
                        'published' => 'Opublikowany',
                        'archived' => 'Zarchiwizowany',
                        default => $state,
                    }),

                TextColumn::make('published_at')
                    ->label('Publikacja')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->placeholder('Nie zaplanowano'),

                IconColumn::make('is_featured')
                    ->label('Wyróżniony')
                    ->boolean()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('views_count')
                    ->label('Wyśw.')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('reading_time')
                    ->label('Min')
                    ->suffix(' min')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Aktualizacja')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Post::getStatusOptions()),

                SelectFilter::make('post_category_id')
                    ->label('Kategoria')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('author_id')
                    ->label('Autor')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_featured')
                    ->label('Wyróżniony'),
            ])
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make()
                    ->excludeAttributes(['slug', 'published_at', 'views_count'])
                    ->beforeReplicaSaved(function (Post $replica): void {
                        $replica->slug = $replica->slug.'-copy-'.now()->timestamp;
                        $replica->status = 'draft';
                        $replica->title = $replica->title.' (Kopia)';
                        $replica->is_featured = false;
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    BulkAction::make('publish')
                        ->label('Publikuj')
                        ->icon(Heroicon::Check)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            /** @var Collection<int, Post> $records */
                            $records->each(fn (Post $record) => $record->update([
                                'status' => 'published',
                                'published_at' => $record->published_at ?? now(),
                            ]));
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('unpublish')
                        ->label('Cofnij publikację')
                        ->icon(Heroicon::XMark)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            /** @var Collection<int, Post> $records */
                            $records->each(fn (Post $record) => $record->update(['status' => 'draft']));
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('feature')
                        ->label('Wyróżnij')
                        ->icon(Heroicon::Star)
                        ->color('info')
                        ->action(function (Collection $records): void {
                            /** @var Collection<int, Post> $records */
                            $records->each(fn (Post $record) => $record->update(['is_featured' => true]));
                        })
                        ->deselectRecordsAfterCompletion(),
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
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
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

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'excerpt', 'content'];
    }
}
