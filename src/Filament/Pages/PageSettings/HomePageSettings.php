<?php

namespace Webfloo\Filament\Pages\PageSettings;

use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class HomePageSettings extends AbstractPageSettings
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Home;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Strona główna';

    protected static ?string $title = 'Strona główna';

    protected static ?string $slug = 'pages/home';

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) config('webfloo.pages.home', true);
    }

    protected function settingsPrefix(): string
    {
        return 'home';
    }

    protected function notificationBody(): string
    {
        return 'Ustawienia strony głównej zostały zapisane.';
    }

    protected static function getPermissionName(): string
    {
        return 'view_home_page_settings';
    }

    /**
     * @return list<string>
     */
    protected function nonTranslatableKeys(): array
    {
        return [
            'hero_cta_url',
            'hero_secondary_cta_url',
            'services_featured_only',
            'portfolio_featured_only',
            'portfolio_limit',
            'testimonials_featured_only',
            'meta_image',
            'partners',
            'about_features',
            'about_stats',
            'blog_limit',
            'blog_view_all_url',
            'features',
            'features_cta_href',
        ];
    }

    public function mount(): void
    {
        $this->form->fill([
            // Hero
            'hero_title' => $this->getSetting('home.hero_title'),
            'hero_subtitle' => $this->getSetting('home.hero_subtitle'),
            'hero_description' => $this->getSetting('home.hero_description'),
            'hero_cta_text' => $this->getSetting('home.hero_cta_text'),
            'hero_cta_url' => $this->getSetting('home.hero_cta_url'),
            'hero_secondary_cta_text' => $this->getSetting('home.hero_secondary_cta_text'),
            'hero_secondary_cta_url' => $this->getSetting('home.hero_secondary_cta_url'),
            // About
            'about_title' => $this->getSetting('home.about_title'),
            'about_subtitle' => $this->getSetting('home.about_subtitle'),
            'about_description' => $this->getSetting('home.about_description'),
            'about_features' => $this->getSetting('home.about_features', []),
            'about_stats' => $this->getSetting('home.about_stats', []),
            'partners' => $this->getSetting('home.partners', []),
            // Services
            'services_title' => $this->getSetting('home.services_title'),
            'services_subtitle' => $this->getSetting('home.services_subtitle'),
            'services_description' => $this->getSetting('home.services_description'),
            'services_featured_only' => $this->getSetting('home.services_featured_only', false),
            // Portfolio
            'portfolio_title' => $this->getSetting('home.portfolio_title'),
            'portfolio_subtitle' => $this->getSetting('home.portfolio_subtitle'),
            'portfolio_description' => $this->getSetting('home.portfolio_description'),
            'portfolio_featured_only' => $this->getSetting('home.portfolio_featured_only', false),
            'portfolio_limit' => $this->getSetting('home.portfolio_limit', 6),
            // Testimonials
            'testimonials_title' => $this->getSetting('home.testimonials_title'),
            'testimonials_subtitle' => $this->getSetting('home.testimonials_subtitle'),
            'testimonials_featured_only' => $this->getSetting('home.testimonials_featured_only', false),
            // FAQ
            'faq_title' => $this->getSetting('home.faq_title'),
            'faq_subtitle' => $this->getSetting('home.faq_subtitle'),
            'faq_description' => $this->getSetting('home.faq_description'),
            // Blog
            'blog_title' => $this->getSetting('home.blog_title'),
            'blog_subtitle' => $this->getSetting('home.blog_subtitle'),
            'blog_description' => $this->getSetting('home.blog_description'),
            'blog_limit' => $this->getSetting('home.blog_limit', 3),
            'blog_view_all_url' => $this->getSetting('home.blog_view_all_url'),
            'blog_view_all_text' => $this->getSetting('home.blog_view_all_text'),
            // Features
            'features_title' => $this->getSetting('home.features_title'),
            'features_subtitle' => $this->getSetting('home.features_subtitle'),
            'features_description' => $this->getSetting('home.features_description'),
            'features' => $this->getSetting('home.features', []),
            'features_cta_text' => $this->getSetting('home.features_cta_text'),
            'features_cta_href' => $this->getSetting('home.features_cta_href'),
            // SEO
            'meta_title' => $this->getSetting('home.meta_title'),
            'meta_description' => $this->getSetting('home.meta_description'),
            'meta_image' => $this->getSetting('home.meta_image'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Sekcje')
                    ->tabs([
                        Tab::make('Hero')
                            ->icon(Heroicon::Star)
                            ->schema([
                                Section::make('Nagłówek główny')
                                    ->description('Pierwsza sekcja widoczna na stronie')
                                    ->schema([
                                        TextInput::make('hero_title')
                                            ->label('Tytuł')
                                            ->maxLength(255)
                                            ->placeholder('Tworzymy oprogramowanie...'),

                                        TextInput::make('hero_subtitle')
                                            ->label('Nadtytuł')
                                            ->maxLength(100)
                                            ->placeholder('Software House'),

                                        Textarea::make('hero_description')
                                            ->label('Opis')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->placeholder('Profesjonalne strony internetowe...'),

                                        TextInput::make('hero_cta_text')
                                            ->label('Tekst przycisku głównego')
                                            ->maxLength(50)
                                            ->placeholder('Współpraca?'),

                                        TextInput::make('hero_cta_url')
                                            ->label('Link przycisku głównego')
                                            ->maxLength(255)
                                            ->placeholder('#contact'),

                                        TextInput::make('hero_secondary_cta_text')
                                            ->label('Tekst przycisku drugorzędnego')
                                            ->maxLength(50)
                                            ->placeholder('Zobacz projekty'),

                                        TextInput::make('hero_secondary_cta_url')
                                            ->label('Link przycisku drugorzędnego')
                                            ->maxLength(255)
                                            ->placeholder('#portfolio'),

                                    ]),
                            ]),

                        Tab::make('O nas')
                            ->icon(Heroicon::UserGroup)
                            ->schema([
                                Section::make('Sekcja "O nas"')
                                    ->schema([
                                        TextInput::make('about_title')
                                            ->label('Tytuł')
                                            ->maxLength(255)
                                            ->placeholder('Dlaczego my?'),

                                        TextInput::make('about_subtitle')
                                            ->label('Nadtytuł')
                                            ->maxLength(100)
                                            ->placeholder('O nas'),

                                        Textarea::make('about_description')
                                            ->label('Opis')
                                            ->rows(4)
                                            ->maxLength(1000),

                                        Repeater::make('about_features')
                                            ->label('Cechy / Zalety')
                                            ->simple(
                                                TextInput::make('feature')
                                                    ->placeholder('Ponad 10 lat doświadczenia...')
                                            )
                                            ->defaultItems(3)
                                            ->maxItems(10)
                                            ->reorderable()
                                            ->collapsible(),

                                        Repeater::make('about_stats')
                                            ->label('Statystyki')
                                            ->schema([
                                                TextInput::make('value')
                                                    ->label('Wartość')
                                                    ->placeholder('150+')
                                                    ->maxLength(20),
                                                TextInput::make('label')
                                                    ->label('Etykieta')
                                                    ->placeholder('Projektow')
                                                    ->maxLength(50),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(3)
                                            ->maxItems(6)
                                            ->reorderable()
                                            ->collapsible(),

                                        Repeater::make('partners')
                                            ->label('Partnerzy / Technologie')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Nazwa')
                                                    ->maxLength(50),
                                                TextInput::make('logo')
                                                    ->label('URL logo')
                                                    ->url()
                                                    ->placeholder('https://cdn.jsdelivr.net/...'),
                                            ])
                                            ->columns(2)
                                            ->maxItems(20)
                                            ->reorderable()
                                            ->collapsible()
                                            ->collapsed(),
                                    ]),
                            ]),

                        Tab::make('Usługi')
                            ->icon(Heroicon::Wrench)
                            ->schema([
                                Section::make('Sekcja usług')
                                    ->description('Usługi pobierane są z zakładki Treści -> Usługi')
                                    ->schema([
                                        TextInput::make('services_title')
                                            ->label('Tytuł')
                                            ->maxLength(255)
                                            ->placeholder('Nasze Usługi'),

                                        TextInput::make('services_subtitle')
                                            ->label('Nadtytuł')
                                            ->maxLength(100)
                                            ->placeholder('Co robimy'),

                                        Textarea::make('services_description')
                                            ->label('Opis')
                                            ->rows(3)
                                            ->maxLength(500),

                                        Toggle::make('services_featured_only')
                                            ->label('Tylko wyróżnione')
                                            ->helperText('Pokaż tylko usługi oznaczone jako "Wyróżnione"'),
                                    ]),
                            ]),

                        Tab::make('Portfolio')
                            ->icon(Heroicon::Briefcase)
                            ->schema([
                                Section::make('Sekcja portfolio')
                                    ->description('Projekty pobierane są z zakładki Treści -> Projekty')
                                    ->schema([
                                        TextInput::make('portfolio_title')
                                            ->label('Tytuł')
                                            ->maxLength(255)
                                            ->placeholder('Nasze Realizacje'),

                                        TextInput::make('portfolio_subtitle')
                                            ->label('Nadtytuł')
                                            ->maxLength(100)
                                            ->placeholder('Portfolio'),

                                        Textarea::make('portfolio_description')
                                            ->label('Opis')
                                            ->rows(3)
                                            ->maxLength(500),

                                        Toggle::make('portfolio_featured_only')
                                            ->label('Tylko wyróżnione')
                                            ->helperText('Pokaż tylko projekty oznaczone jako "Wyróżnione"'),

                                        TextInput::make('portfolio_limit')
                                            ->label('Maksymalna liczba projektów')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(20)
                                            ->default(6),
                                    ]),
                            ]),

                        Tab::make('Opinie')
                            ->icon(Heroicon::ChatBubbleLeftRight)
                            ->schema([
                                Section::make('Sekcja opinii')
                                    ->description('Opinie pobierane są z zakładki Treści -> Opinie')
                                    ->schema([
                                        TextInput::make('testimonials_title')
                                            ->label('Tytuł')
                                            ->maxLength(255)
                                            ->placeholder('Co mówią nasi klienci'),

                                        TextInput::make('testimonials_subtitle')
                                            ->label('Nadtytuł')
                                            ->maxLength(100)
                                            ->placeholder('Opinie'),

                                        Toggle::make('testimonials_featured_only')
                                            ->label('Tylko wyróżnione')
                                            ->helperText('Pokaż tylko opinie oznaczone jako "Wyróżnione"'),
                                    ]),
                            ]),

                        Tab::make('FAQ')
                            ->icon(Heroicon::QuestionMarkCircle)
                            ->schema([
                                Section::make('Sekcja FAQ')
                                    ->description('Pytania pobierane są z zakładki Treści -> FAQ')
                                    ->schema([
                                        TextInput::make('faq_title')
                                            ->label('Tytuł')
                                            ->maxLength(255)
                                            ->placeholder('Często zadawane pytania'),

                                        TextInput::make('faq_subtitle')
                                            ->label('Nadtytuł')
                                            ->maxLength(100)
                                            ->placeholder('FAQ'),

                                        Textarea::make('faq_description')
                                            ->label('Opis')
                                            ->rows(3)
                                            ->maxLength(500),
                                    ]),
                            ]),

                        Tab::make('Blog')
                            ->icon(Heroicon::Newspaper)
                            ->schema([
                                Section::make('Sekcja bloga')
                                    ->description('Najnowsze wpisy z bloga wyświetlane na stronie głównej')
                                    ->schema([
                                        TextInput::make('blog_title')
                                            ->label('Tytuł')
                                            ->maxLength(255)
                                            ->placeholder('Z naszego bloga'),

                                        TextInput::make('blog_subtitle')
                                            ->label('Nadtytuł')
                                            ->maxLength(100)
                                            ->placeholder('Blog'),

                                        Textarea::make('blog_description')
                                            ->label('Opis')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->placeholder('Praktyczna wiedza o tworzeniu oprogramowania...'),

                                        TextInput::make('blog_limit')
                                            ->label('Liczba wyświetlanych postów')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(12)
                                            ->default(3),

                                        TextInput::make('blog_view_all_url')
                                            ->label('URL "Zobacz wszystkie"')
                                            ->maxLength(255)
                                            ->placeholder('/blog'),

                                        TextInput::make('blog_view_all_text')
                                            ->label('Tekst "Zobacz wszystkie"')
                                            ->maxLength(100)
                                            ->placeholder('Wszystkie artykuły'),
                                    ]),
                            ]),

                        Tab::make('Współpraca')
                            ->icon(Heroicon::Squares2x2)
                            ->schema([
                                Section::make('Sekcja współpracy')
                                    ->description('Siatka cech procesu współpracy')
                                    ->schema([
                                        TextInput::make('features_title')
                                            ->label('Tytuł')
                                            ->maxLength(255)
                                            ->placeholder('Zarządzanie projektem bez stresu'),

                                        TextInput::make('features_subtitle')
                                            ->label('Nadtytuł')
                                            ->maxLength(100)
                                            ->placeholder('Współpraca'),

                                        Textarea::make('features_description')
                                            ->label('Opis')
                                            ->rows(3)
                                            ->maxLength(500),

                                        Repeater::make('features')
                                            ->label('Cechy')
                                            ->schema([
                                                TextInput::make('icon')
                                                    ->label('Ikona (Tabler)')
                                                    ->placeholder('clipboard-check')
                                                    ->maxLength(50),

                                                TextInput::make('title')
                                                    ->label('Tytuł')
                                                    ->maxLength(100),

                                                Textarea::make('description')
                                                    ->label('Opis')
                                                    ->rows(2)
                                                    ->maxLength(300),
                                            ])
                                            ->columns(1)
                                            ->defaultItems(4)
                                            ->maxItems(8)
                                            ->reorderable()
                                            ->collapsible(),

                                        TextInput::make('features_cta_text')
                                            ->label('Tekst przycisku CTA')
                                            ->maxLength(100)
                                            ->placeholder('Rozpocznij projekt'),

                                        TextInput::make('features_cta_href')
                                            ->label('Link przycisku CTA')
                                            ->maxLength(255)
                                            ->placeholder('#contact'),
                                    ]),
                            ]),

                        Tab::make('SEO')
                            ->icon(Heroicon::MagnifyingGlass)
                            ->schema([
                                Section::make('Optymalizacja SEO')
                                    ->schema([
                                        TextInput::make('meta_title')
                                            ->label('Meta Title')
                                            ->maxLength(70)
                                            ->helperText('Zalecane: 50-60 znaków'),

                                        Textarea::make('meta_description')
                                            ->label('Meta Description')
                                            ->rows(3)
                                            ->maxLength(160)
                                            ->helperText('Zalecane: 150-160 znaków'),

                                        FileUpload::make('meta_image')
                                            ->label('Obrazek Open Graph')
                                            ->image()
                                            ->disk('public')
                                            ->directory('pages/home/meta')
                                            ->imageResizeMode('cover')
                                            ->imageCropAspectRatio('1200:630')
                                            ->maxSize(2048)
                                            ->helperText('Zalecany rozmiar: 1200x630 px'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }
}
