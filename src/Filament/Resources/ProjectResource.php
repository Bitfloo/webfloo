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
                    Tab::make('Podstawowe')
                        ->icon(Heroicon::InformationCircle)
                        ->schema([
                            Section::make('Informacje')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('title')
                                        ->label('Tytuł')
                                        ->required()
                                        ->maxLength(200)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

                                    TextInput::make('slug')
                                        ->label('Slug')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(200)
                                        ->alphaDash(),

                                    Select::make('category')
                                        ->label('Kategoria')
                                        ->options(Project::getCategoryOptions())
                                        ->native(false)
                                        ->searchable(),

                                    Select::make('industry')
                                        ->label('Branża')
                                        ->options(Project::getIndustryOptions())
                                        ->native(false)
                                        ->searchable(),

                                    TextInput::make('client')
                                        ->label('Klient')
                                        ->maxLength(100),

                                    TextInput::make('url')
                                        ->label('URL projektu')
                                        ->url()
                                        ->maxLength(255)
                                        ->placeholder('https://...'),

                                    Textarea::make('excerpt')
                                        ->label('Krótki opis')
                                        ->rows(2)
                                        ->maxLength(300)
                                        ->columnSpanFull()
                                        ->helperText('Wyświetlany na liście projektów'),

                                    RichEditor::make('description')
                                        ->label('Opis ogólny')
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

                            Section::make('Szczegóły projektu')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('duration')
                                        ->label('Czas realizacji')
                                        ->placeholder('np. 3 miesiące')
                                        ->maxLength(50),

                                    TextInput::make('team_size')
                                        ->label('Zespół')
                                        ->placeholder('np. 5 programistów')
                                        ->maxLength(50),

                                    TagsInput::make('technologies')
                                        ->label('Technologie')
                                        ->placeholder('Dodaj technologię...')
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

                    Tab::make('Case Study')
                        ->icon(Heroicon::DocumentText)
                        ->schema([
                            Section::make('Wyzwanie')
                                ->description('Opisz problem lub wyzwanie przed którym stał klient')
                                ->schema([
                                    RichEditor::make('challenge')
                                        ->label('Wyzwanie')
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                            'link',
                                            'bulletList',
                                            'orderedList',
                                        ])
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Rozwiązanie')
                                ->description('Opisz jak rozwiązaliście problem')
                                ->schema([
                                    RichEditor::make('solution')
                                        ->label('Rozwiązanie')
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                            'link',
                                            'bulletList',
                                            'orderedList',
                                        ])
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Rezultaty')
                                ->description('Jakie efekty przyniosło wdrożenie')
                                ->schema([
                                    RichEditor::make('results')
                                        ->label('Rezultaty')
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

                    Tab::make('Metryki')
                        ->icon(Heroicon::ChartBar)
                        ->schema([
                            Section::make('Kluczowe metryki')
                                ->description('Liczby pokazujące sukces projektu (np. "2M+" - "Pobrania aplikacji")')
                                ->schema([
                                    Repeater::make('metrics')
                                        ->label('Metryki')
                                        ->schema([
                                            TextInput::make('value')
                                                ->label('Wartość')
                                                ->placeholder('np. 2M+, 98%, 50%')
                                                ->required()
                                                ->maxLength(20),
                                            TextInput::make('label')
                                                ->label('Opis')
                                                ->placeholder('np. Pobrania aplikacji')
                                                ->required()
                                                ->maxLength(100),
                                        ])
                                        ->columns(2)
                                        ->defaultItems(0)
                                        ->reorderable()
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): string => (is_string($state['value'] ?? null) ? $state['value'] : '').' - '.(is_string($state['label'] ?? null) ? $state['label'] : '')),
                                ]),

                            Section::make('Osiągnięcia')
                                ->description('Nagrody, wyróżnienia, certyfikaty')
                                ->schema([
                                    TagsInput::make('achievements')
                                        ->label('Osiągnięcia')
                                        ->placeholder('Dodaj osiągnięcie...')
                                        ->helperText('Np. "Featured in App Store", "Fintech Innovation Award"'),
                                ]),
                        ]),

                    Tab::make('Media')
                        ->icon(Heroicon::Photo)
                        ->schema([
                            Section::make('Główne zdjęcie')
                                ->schema([
                                    FileUpload::make('image')
                                        ->label('Zdjęcie główne')
                                        ->image()
                                        ->directory('projects')
                                        ->visibility('public')
                                        ->maxSize(2048)
                                        ->imageEditor()
                                        ->helperText('Wyświetlane na liście projektów i w hero'),
                                ]),

                            Section::make('Galeria')
                                ->description('Dodatkowe zdjęcia, screenshoty')
                                ->schema([
                                    FileUpload::make('gallery')
                                        ->label('Galeria')
                                        ->image()
                                        ->multiple()
                                        ->reorderable()
                                        ->directory('projects/gallery')
                                        ->visibility('public')
                                        ->maxSize(2048)
                                        ->maxFiles(10),
                                ]),

                            Section::make('Video')
                                ->schema([
                                    TextInput::make('video_url')
                                        ->label('URL video')
                                        ->url()
                                        ->placeholder('https://youtube.com/watch?v=...')
                                        ->helperText('Link do YouTube lub Vimeo'),
                                ]),
                        ]),

                    Tab::make('Testimonial')
                        ->icon(Heroicon::ChatBubbleLeftRight)
                        ->schema([
                            Section::make('Referencja klienta')
                                ->description('Opinia klienta specyficzna dla tego projektu')
                                ->columns(2)
                                ->schema([
                                    Textarea::make('testimonial_quote')
                                        ->label('Cytat')
                                        ->rows(4)
                                        ->columnSpanFull()
                                        ->placeholder('Co klient powiedział o współpracy...'),

                                    TextInput::make('testimonial_author')
                                        ->label('Autor')
                                        ->placeholder('Jan Kowalski')
                                        ->maxLength(100),

                                    TextInput::make('testimonial_role')
                                        ->label('Stanowisko')
                                        ->placeholder('CEO')
                                        ->maxLength(100),

                                    TextInput::make('testimonial_company')
                                        ->label('Firma')
                                        ->placeholder('Nazwa firmy')
                                        ->maxLength(100),

                                    FileUpload::make('testimonial_avatar')
                                        ->label('Zdjęcie autora')
                                        ->image()
                                        ->avatar()
                                        ->directory('testimonials')
                                        ->visibility('public')
                                        ->maxSize(512),
                                ]),
                        ]),

                    Tab::make('Ustawienia')
                        ->icon(Heroicon::Cog6Tooth)
                        ->schema([
                            Section::make('Widoczność')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('sort_order')
                                        ->label('Kolejność')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0),

                                    Toggle::make('is_featured')
                                        ->label('Wyróżniany')
                                        ->default(false)
                                        ->helperText('Pokazuj na stronie głównej'),

                                    Toggle::make('is_active')
                                        ->label('Aktywny')
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
                    ->label('Zdjęcie')
                    ->size(60)
                    ->square(),

                TextColumn::make('title')
                    ->label('Tytuł')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Project $record): ?string => $record->client),

                TextColumn::make('industry')
                    ->label('Branża')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => $state ? (Project::getIndustryOptions()[$state] ?? $state) : '-'),

                TextColumn::make('category')
                    ->label('Kategoria')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? (Project::getCategoryOptions()[$state] ?? $state) : '-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('technologies')
                    ->label('Technologie')
                    ->badge()
                    ->separator(',')
                    ->limitList(3)
                    ->toggleable(),

                IconColumn::make('is_featured')
                    ->label('Wyróżniany')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Aktywny')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('industry')
                    ->label('Branża')
                    ->options(Project::getIndustryOptions()),

                SelectFilter::make('category')
                    ->label('Kategoria')
                    ->options(Project::getCategoryOptions()),

                TernaryFilter::make('is_featured')
                    ->label('Wyróżniany'),

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
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }
}
