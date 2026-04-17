<?php

namespace Webfloo\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Webfloo\Filament\Resources\ProjectResource\Pages\CreateProject;
use Webfloo\Filament\Resources\ProjectResource\Pages\EditProject;
use Webfloo\Filament\Resources\ProjectResource\Pages\ListProjects;
use Webfloo\Models\Project;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::RectangleStack;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string
    {
        return __('Treści');
    }

    public static function getModelLabel(): string
    {
        return __('Projekt');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Portfolio');
    }

    public static function canAccess(): bool
    {
        if (! config('webfloo.features.portfolio', true)) {
            return false;
        }

        return auth()->user()?->can('view_any_project') === true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Projekt')
                ->tabs([
                    Tab::make(__('Podstawowe'))
                        ->icon(Heroicon::InformationCircle)
                        ->schema([
                            Section::make(__('Informacje'))
                                ->columns(2)
                                ->schema([
                                    TextInput::make('title')
                                        ->label(__('Tytuł'))
                                        ->required()
                                        ->maxLength(200)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

                                    TextInput::make('slug')
                                        ->label(__('Slug'))
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(200)
                                        ->alphaDash(),

                                    Select::make('category')
                                        ->label(__('Kategoria'))
                                        ->options(Project::getCategoryOptions())
                                        ->native(false)
                                        ->searchable(),

                                    Select::make('industry')
                                        ->label(__('Branża'))
                                        ->options(Project::getIndustryOptions())
                                        ->native(false)
                                        ->searchable(),

                                    TextInput::make('client')
                                        ->label(__('Klient'))
                                        ->maxLength(100),

                                    TextInput::make('url')
                                        ->label(__('URL projektu'))
                                        ->url()
                                        ->maxLength(255)
                                        ->placeholder('https://...'),

                                    Textarea::make('excerpt')
                                        ->label(__('Krótki opis'))
                                        ->rows(2)
                                        ->maxLength(300)
                                        ->columnSpanFull()
                                        ->helperText(__('Wyświetlany na liście projektów')),

                                    RichEditor::make('description')
                                        ->label(__('Opis ogólny'))
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                            'link',
                                            'bulletList',
                                            'orderedList',
                                            'h2',
                                            'h3',
                                        ])
                                        ->columnSpanFull(),
                                ]),

                            Section::make(__('Szczegóły projektu'))
                                ->columns(3)
                                ->schema([
                                    TextInput::make('duration')
                                        ->label(__('Czas realizacji'))
                                        ->placeholder(__('np. 3 miesiące'))
                                        ->maxLength(50),

                                    TextInput::make('team_size')
                                        ->label(__('Zespół'))
                                        ->placeholder(__('np. 5 programistów'))
                                        ->maxLength(50),

                                    TagsInput::make('technologies')
                                        ->label(__('Technologie'))
                                        ->placeholder(__('Dodaj technologię...'))
                                        ->suggestions([
                                            'PHP',
                                            'Laravel',
                                            'Vue.js',
                                            'React',
                                            'TypeScript',
                                            'MySQL',
                                            'PostgreSQL',
                                            'Redis',
                                            'Docker',
                                            'AWS',
                                            'Tailwind CSS',
                                            'Filament',
                                            'Livewire',
                                            'Next.js',
                                            'Node.js',
                                            'Python',
                                            'Flutter',
                                            'React Native',
                                        ]),
                                ]),
                        ]),

                    Tab::make(__('Case Study'))
                        ->icon(Heroicon::DocumentText)
                        ->schema([
                            Section::make(__('Wyzwanie'))
                                ->description(__('Opisz problem lub wyzwanie przed którym stał klient'))
                                ->schema([
                                    RichEditor::make('challenge')
                                        ->label(__('Wyzwanie'))
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                            'link',
                                            'bulletList',
                                            'orderedList',
                                        ])
                                        ->columnSpanFull(),
                                ]),

                            Section::make(__('Rozwiązanie'))
                                ->description(__('Opisz jak rozwiązaliście problem'))
                                ->schema([
                                    RichEditor::make('solution')
                                        ->label(__('Rozwiązanie'))
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                            'link',
                                            'bulletList',
                                            'orderedList',
                                        ])
                                        ->columnSpanFull(),
                                ]),

                            Section::make(__('Rezultaty'))
                                ->description(__('Jakie efekty przyniosło wdrożenie'))
                                ->schema([
                                    RichEditor::make('results')
                                        ->label(__('Rezultaty'))
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                            'link',
                                            'bulletList',
                                            'orderedList',
                                        ])
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Tab::make(__('Metryki'))
                        ->icon(Heroicon::ChartBar)
                        ->schema([
                            Section::make(__('Kluczowe metryki'))
                                ->description(__('Liczby pokazujące sukces projektu (np. "2M+" - "Pobrania aplikacji")'))
                                ->schema([
                                    Repeater::make('metrics')
                                        ->label(__('Metryki'))
                                        ->schema([
                                            TextInput::make('value')
                                                ->label(__('Wartość'))
                                                ->placeholder(__('np. 2M+, 98%, 50%'))
                                                ->required()
                                                ->maxLength(20),
                                            TextInput::make('label')
                                                ->label(__('Opis'))
                                                ->placeholder(__('np. Pobrania aplikacji'))
                                                ->required()
                                                ->maxLength(100),
                                        ])
                                        ->columns(2)
                                        ->defaultItems(0)
                                        ->reorderable()
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): string => (is_string($state['value'] ?? null) ? $state['value'] : '').' - '.(is_string($state['label'] ?? null) ? $state['label'] : '')),
                                ]),

                            Section::make(__('Osiągnięcia'))
                                ->description(__('Nagrody, wyróżnienia, certyfikaty'))
                                ->schema([
                                    TagsInput::make('achievements')
                                        ->label(__('Osiągnięcia'))
                                        ->placeholder(__('Dodaj osiągnięcie...'))
                                        ->helperText(__('Np. "Featured in App Store", "Fintech Innovation Award"')),
                                ]),
                        ]),

                    Tab::make(__('Media'))
                        ->icon(Heroicon::Photo)
                        ->schema([
                            Section::make(__('Główne zdjęcie'))
                                ->schema([
                                    FileUpload::make('image')
                                        ->label(__('Zdjęcie główne'))
                                        ->image()
                                        ->directory('projects')
                                        ->visibility('public')
                                        ->maxSize(2048)
                                        ->imageEditor()
                                        ->helperText(__('Wyświetlane na liście projektów i w hero')),
                                ]),

                            Section::make(__('Galeria'))
                                ->description(__('Dodatkowe zdjęcia, screenshoty'))
                                ->schema([
                                    FileUpload::make('gallery')
                                        ->label(__('Galeria'))
                                        ->image()
                                        ->multiple()
                                        ->reorderable()
                                        ->directory('projects/gallery')
                                        ->visibility('public')
                                        ->maxSize(2048)
                                        ->maxFiles(10),
                                ]),

                            Section::make(__('Video'))
                                ->schema([
                                    TextInput::make('video_url')
                                        ->label(__('URL video'))
                                        ->url()
                                        ->placeholder('https://youtube.com/watch?v=...')
                                        ->helperText(__('Link do YouTube lub Vimeo')),
                                ]),
                        ]),

                    Tab::make(__('Testimonial'))
                        ->icon(Heroicon::ChatBubbleLeftRight)
                        ->schema([
                            Section::make(__('Referencja klienta'))
                                ->description(__('Opinia klienta specyficzna dla tego projektu'))
                                ->columns(2)
                                ->schema([
                                    Textarea::make('testimonial_quote')
                                        ->label(__('Cytat'))
                                        ->rows(4)
                                        ->columnSpanFull()
                                        ->placeholder(__('Co klient powiedział o współpracy...')),

                                    TextInput::make('testimonial_author')
                                        ->label(__('Autor'))
                                        ->placeholder('Jan Kowalski')
                                        ->maxLength(100),

                                    TextInput::make('testimonial_role')
                                        ->label(__('Stanowisko'))
                                        ->placeholder('CEO')
                                        ->maxLength(100),

                                    TextInput::make('testimonial_company')
                                        ->label(__('Firma'))
                                        ->placeholder(__('Nazwa firmy'))
                                        ->maxLength(100),

                                    FileUpload::make('testimonial_avatar')
                                        ->label(__('Zdjęcie autora'))
                                        ->image()
                                        ->avatar()
                                        ->directory('testimonials')
                                        ->visibility('public')
                                        ->maxSize(512),
                                ]),
                        ]),

                    Tab::make(__('Ustawienia'))
                        ->icon(Heroicon::Cog6Tooth)
                        ->schema([
                            Section::make(__('Widoczność'))
                                ->columns(3)
                                ->schema([
                                    TextInput::make('sort_order')
                                        ->label(__('Kolejność'))
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0),

                                    Toggle::make('is_featured')
                                        ->label(__('Wyróżniany'))
                                        ->default(false)
                                        ->helperText(__('Pokazuj na stronie głównej')),

                                    Toggle::make('is_active')
                                        ->label(__('Aktywny'))
                                        ->default(true),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
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

                ImageColumn::make('image')
                    ->label(__('Zdjęcie'))
                    ->size(60)
                    ->square(),

                TextColumn::make('title')
                    ->label(__('Tytuł'))
                    ->searchable()
                    ->sortable()
                    ->description(fn (Project $record): ?string => $record->client),

                TextColumn::make('industry')
                    ->label(__('Branża'))
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => $state ? (Project::getIndustryOptions()[$state] ?? $state) : '-'),

                TextColumn::make('category')
                    ->label(__('Kategoria'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? (Project::getCategoryOptions()[$state] ?? $state) : '-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('technologies')
                    ->label(__('Technologie'))
                    ->badge()
                    ->separator(',')
                    ->limitList(3)
                    ->toggleable(),

                IconColumn::make('is_featured')
                    ->label(__('Wyróżniany'))
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label(__('Aktywny'))
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('industry')
                    ->label(__('Branża'))
                    ->options(Project::getIndustryOptions()),

                SelectFilter::make('category')
                    ->label(__('Kategoria'))
                    ->options(Project::getCategoryOptions()),

                TernaryFilter::make('is_featured')
                    ->label(__('Wyróżniany')),

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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }
}
