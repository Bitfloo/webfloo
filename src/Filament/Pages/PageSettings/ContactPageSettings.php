<?php

namespace Webfloo\Filament\Pages\PageSettings;

use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ContactPageSettings extends AbstractPageSettings
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Envelope;

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Kontakt';

    protected static ?string $title = 'Kontakt';

    protected static ?string $slug = 'pages/contact';

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) config('webfloo.pages.contact', true);
    }

    protected function settingsPrefix(): string
    {
        return 'contact';
    }

    protected function notificationBody(): string
    {
        return 'Ustawienia strony kontakt zostały zapisane.';
    }

    protected static function getPermissionName(): string
    {
        return 'view_contact_page_settings';
    }

    public function mount(): void
    {
        $this->form->fill([
            'page_title' => $this->getSetting('contact.page_title'),
            'page_subtitle' => $this->getSetting('contact.page_subtitle'),
            'page_description' => $this->getSetting('contact.page_description'),
            'business_hours' => $this->getSetting('contact.business_hours'),
            'response_time' => $this->getSetting('contact.response_time'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Nagłówek strony kontakt')
                    ->description('Dane kontaktowe (email, telefon, adres) pobierane są z Ustawienia -> Kontakt')
                    ->schema([
                        TextInput::make('page_title')
                            ->label('Tytuł')
                            ->maxLength(255)
                            ->placeholder('Skontaktuj się z nami'),

                        TextInput::make('page_subtitle')
                            ->label('Nadtytuł')
                            ->maxLength(100)
                            ->placeholder('Kontakt'),

                        Textarea::make('page_description')
                            ->label('Opis')
                            ->rows(3)
                            ->maxLength(500),
                    ]),

                Section::make('Godziny pracy')
                    ->description('Tekst wyświetlany w karcie godzin pracy na stronie kontakt')
                    ->schema([
                        TextInput::make('business_hours')
                            ->label('Godziny pracy')
                            ->maxLength(255)
                            ->placeholder('Pon-Pt: 9:00 - 17:00')
                            ->helperText('Np. "Pon-Pt: 9:00 - 17:00" (PL) lub "Mon-Fri: 9:00 AM - 5:00 PM" (EN)'),

                        TextInput::make('response_time')
                            ->label('Czas odpowiedzi')
                            ->maxLength(100)
                            ->placeholder('24h')
                            ->helperText('Np. "24h" -- wyświetlane w informacji o czasie odpowiedzi'),
                    ]),
            ])
            ->statePath('data');
    }
}
