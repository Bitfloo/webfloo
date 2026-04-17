<?php

namespace Webfloo\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;
use Webfloo\Filament\Pages\PageSettings\AbstractPageSettings;

/**
 * Site-wide settings (general, contact, social, integrations).
 *
 * Extends AbstractPageSettings with empty prefix (flat keys)
 * and multi-group storage for logical organization.
 *
 * @property-read Schema $form
 */
class SiteSettings extends AbstractPageSettings
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;

    protected static ?string $title = 'Ustawienia strony';

    protected static ?string $slug = 'settings';

    protected string $view = 'webfloo::filament.pages.site-settings';

    protected function settingsPrefix(): string
    {
        return '';
    }

    protected function notificationBody(): string
    {
        return 'Ustawienia strony zostały zapisane.';
    }

    protected static function getPermissionName(): string
    {
        return 'view_site_settings';
    }

    /**
     * @return list<string>
     */
    protected function nonTranslatableKeys(): array
    {
        return [
            'site_theme',
            'logo',
            'favicon',
            'header_cta_href',
            'contact_email',
            'contact_phone',
            'google_maps_url',
            'social_facebook',
            'social_linkedin',
            'social_instagram',
            'social_github',
            'google_analytics_id',
            'google_tag_manager_id',
            'custom_head_scripts',
        ];
    }

    /**
     * Group settings by key prefix for logical organization.
     */
    protected function settingsGroup(string $key): string
    {
        return match (true) {
            str_starts_with($key, 'social_') => 'social',
            str_starts_with($key, 'contact_') => 'contact',
            str_starts_with($key, 'google_') => 'integrations',
            $key === 'custom_head_scripts' => 'integrations',
            default => 'general',
        };
    }

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => $this->getSetting('site_name'),
            'site_description' => $this->getSetting('site_description'),
            'site_theme' => $this->getSetting('site_theme', 'bitfloo-dark'),
            'logo' => $this->getSetting('logo'),
            'favicon' => $this->getSetting('favicon'),
            'contact_email' => $this->getSetting('contact_email'),
            'contact_phone' => $this->getSetting('contact_phone'),
            'contact_address' => $this->getSetting('contact_address'),
            'google_maps_url' => $this->getSetting('google_maps_url'),
            'social_facebook' => $this->getSetting('social_facebook'),
            'social_linkedin' => $this->getSetting('social_linkedin'),
            'social_instagram' => $this->getSetting('social_instagram'),
            'social_github' => $this->getSetting('social_github'),
            'google_analytics_id' => $this->getSetting('google_analytics_id'),
            'google_tag_manager_id' => $this->getSetting('google_tag_manager_id'),
            'custom_head_scripts' => $this->getSetting('custom_head_scripts'),
            'header_cta_text' => $this->getSetting('header_cta_text', 'Bezpłatna wycena'),
            'header_cta_href' => $this->getSetting('header_cta_href', '#contact'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('Ogólne')
                            ->icon(Heroicon::BuildingOffice)
                            ->schema([
                                TextInput::make('site_name')
                                    ->label('Nazwa strony')
                                    ->required()
                                    ->maxLength(100),

                                Textarea::make('site_description')
                                    ->label('Opis strony (SEO)')
                                    ->rows(3)
                                    ->maxLength(300),

                                Select::make('site_theme')
                                    ->label('Motyw strony')
                                    ->options([
                                        'bitfloo' => 'Bitfloo Light',
                                        'bitfloo-dark' => 'Bitfloo Dark (domyślny)',
                                        'light' => 'FlyonUI Light',
                                        'dark' => 'FlyonUI Dark',
                                        'gourmet' => 'Gourmet',
                                        'corporate' => 'Corporate',
                                        'luxury' => 'Luxury',
                                        'soft' => 'Soft',
                                        'slack' => 'Slack',
                                        'discord' => 'Discord',
                                        'spotify' => 'Spotify',
                                        'facebook' => 'Facebook',
                                        'shadcn' => 'shadcn/ui',
                                    ])
                                    ->default('bitfloo-dark')
                                    ->native(false)
                                    ->searchable()
                                    ->helperText('Wybierz motyw kolorystyczny dla strony publicznej'),

                                FileUpload::make('logo')
                                    ->label('Logo')
                                    ->image()
                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'])
                                    ->directory('brand')
                                    ->imageResizeMode('contain')
                                    ->imageCropAspectRatio(null)
                                    ->maxSize(1024),

                                FileUpload::make('favicon')
                                    ->label('Favicon')
                                    ->image()
                                    ->directory('brand')
                                    ->acceptedFileTypes(['image/x-icon', 'image/png', 'image/svg+xml'])
                                    ->maxSize(512),

                                TextInput::make('header_cta_text')
                                    ->label('Tekst przycisku CTA (Header)')
                                    ->default('Bezpłatna wycena')
                                    ->maxLength(50),

                                TextInput::make('header_cta_href')
                                    ->label('Link przycisku CTA (Header)')
                                    ->default('#contact')
                                    ->maxLength(255),
                            ]),

                        Tab::make('Kontakt')
                            ->icon(Heroicon::Phone)
                            ->schema([
                                TextInput::make('contact_email')
                                    ->label('Email kontaktowy')
                                    ->email()
                                    ->maxLength(255),

                                TextInput::make('contact_phone')
                                    ->label('Telefon')
                                    ->tel()
                                    ->maxLength(30),

                                Textarea::make('contact_address')
                                    ->label('Adres')
                                    ->rows(2),

                                TextInput::make('google_maps_url')
                                    ->label('Link do Google Maps')
                                    ->url(),
                            ]),

                        Tab::make('Social Media')
                            ->icon(Heroicon::Share)
                            ->schema([
                                TextInput::make('social_facebook')
                                    ->label('Facebook')
                                    ->url()
                                    ->prefix('https://'),

                                TextInput::make('social_linkedin')
                                    ->label('LinkedIn')
                                    ->url()
                                    ->prefix('https://'),

                                TextInput::make('social_instagram')
                                    ->label('Instagram')
                                    ->url()
                                    ->prefix('https://'),

                                TextInput::make('social_github')
                                    ->label('GitHub')
                                    ->url()
                                    ->prefix('https://'),
                            ]),

                        Tab::make('Integracje')
                            ->icon(Heroicon::PuzzlePiece)
                            ->schema([
                                TextInput::make('google_analytics_id')
                                    ->label('Google Analytics ID')
                                    ->placeholder('G-XXXXXXXXXX'),

                                TextInput::make('google_tag_manager_id')
                                    ->label('Google Tag Manager ID')
                                    ->placeholder('GTM-XXXXXXX'),

                                Textarea::make('custom_head_scripts')
                                    ->label('Dodatkowe skrypty (head)')
                                    ->rows(5)
                                    ->helperText('Kod zostanie wstawiony przed zamknięciem tagu </head>'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }
}
