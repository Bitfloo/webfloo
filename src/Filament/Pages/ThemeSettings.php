<?php

declare(strict_types=1);

namespace Webfloo\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;
use Webfloo\Services\ThemeService;

/**
 * Theme settings page - SSOT for visual configuration.
 *
 * @property-read Schema $form
 */
class ThemeSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Swatch;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 99;

    protected static ?string $title = 'Wygląd';

    protected static ?string $navigationLabel = 'Wygląd';

    protected static ?string $slug = 'theme-settings';

    protected string $view = 'webfloo::filament.pages.theme-settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    protected ThemeService $themeService;

    public function boot(): void
    {
        $this->themeService = app(ThemeService::class);
    }

    public function mount(): void
    {
        $this->themeService = app(ThemeService::class);
        $config = $this->themeService->getConfig();

        $colors = is_array($config['colors'] ?? null) ? $config['colors'] : [];
        $style = is_array($config['style'] ?? null) ? $config['style'] : [];
        $custom = is_array($config['custom'] ?? null) ? $config['custom'] : [];

        $this->form->fill([
            'base_theme' => $config['base_theme'] ?? 'bitfloo-dark',
            'mode' => $config['mode'] ?? 'dark',
            'primary_color' => $colors['primary'] ?? '#3b82f6',
            'accent_color' => $colors['accent'] ?? '#10b981',
            'roundness' => $style['roundness'] ?? 'default',
            'density' => $style['density'] ?? 'comfortable',
            'custom_css' => $custom['css'] ?? '',
            'custom_js' => $custom['js'] ?? '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $this->themeService = app(ThemeService::class);

        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        // Left column - Form fields
                        Section::make('Ustawienia motywu')
                            ->description('Dostosuj wygląd strony do swojej marki')
                            ->schema([
                                Select::make('base_theme')
                                    ->label('Motyw bazowy')
                                    ->options($this->themeService->getAvailableThemes())
                                    ->default('bitfloo-dark')
                                    ->native(false)
                                    ->searchable()
                                    ->helperText('Podstawa dla wszystkich niezdefiniowanych wartości'),

                                Radio::make('mode')
                                    ->label('Tryb kolorystyczny')
                                    ->options($this->themeService->getModeOptions())
                                    ->default('dark')
                                    ->inline()
                                    ->inlineLabel(false),

                                ColorPicker::make('primary_color')
                                    ->label('Kolor główny (Primary)')
                                    ->default('#3b82f6')
                                    ->helperText('Przyciski, linki, nagłówki')
                                    ->live(debounce: 500),

                                ColorPicker::make('accent_color')
                                    ->label('Kolor akcentowy (Accent)')
                                    ->default('#10b981')
                                    ->helperText('Wyróżnienia, badge, CTA')
                                    ->live(debounce: 500),

                                Select::make('roundness')
                                    ->label('Zaokrąglenie rogów')
                                    ->options($this->themeService->getRoundnessOptions())
                                    ->default('default')
                                    ->native(false)
                                    ->helperText('Styl krawędzi przycisków i kart'),

                                Select::make('density')
                                    ->label('Gęstość interfejsu')
                                    ->options($this->themeService->getDensityOptions())
                                    ->default('comfortable')
                                    ->native(false)
                                    ->helperText('Przestrzeń wewnętrzna elementów'),
                            ])
                            ->columns(1)
                            ->columnSpan(1),

                        // Right column - Preview
                        Section::make('Podgląd')
                            ->description('Jak będą wyglądać elementy')
                            ->schema([
                                View::make('webfloo::filament.pages.theme-preview'),
                            ])
                            ->columnSpan(1),
                    ]),

                // Custom CSS/JS Section
                Section::make('Własny kod')
                    ->description('Globalny CSS i JavaScript wstrzykiwany na wszystkich stronach')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Textarea::make('custom_css')
                                    ->label('Custom CSS')
                                    ->placeholder(".my-class {\n    color: red;\n}")
                                    ->rows(8)
                                    ->helperText('Wstrzykiwany w <head> po zmiennych CSS'),

                                // Custom JS jest stored-XSS surface — gate'owane flagą
                                // `bitfloo.features.custom_js` (default false). Pole
                                // widoczne tylko gdy flag włączony w host configu.
                                Textarea::make('custom_js')
                                    ->label('Custom JavaScript')
                                    ->placeholder("console.log('Hello!');\n// Twój kod JS")
                                    ->rows(8)
                                    ->helperText('Wstrzykiwany przed </body>. UWAGA: admin-authored JS wykonuje się dla wszystkich odwiedzających.')
                                    ->visible(fn (): bool => (bool) config('webfloo.features.custom_js', false)),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ])
            ->statePath('data');
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset')
                ->label('Przywróć domyślne')
                ->icon(Heroicon::ArrowPath)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Przywrócić domyślne ustawienia?')
                ->modalDescription('Wszystkie zmiany zostaną utracone.')
                ->action(function (): void {
                    $defaults = $this->themeService->getDefaults();
                    $defColors = is_array($defaults['colors'] ?? null) ? $defaults['colors'] : [];
                    $defStyle = is_array($defaults['style'] ?? null) ? $defaults['style'] : [];
                    $defCustom = is_array($defaults['custom'] ?? null) ? $defaults['custom'] : [];

                    $this->form->fill([
                        'base_theme' => $defaults['base_theme'] ?? 'bitfloo-dark',
                        'mode' => $defaults['mode'] ?? 'dark',
                        'primary_color' => $defColors['primary'] ?? '#3b82f6',
                        'accent_color' => $defColors['accent'] ?? '#10b981',
                        'roundness' => $defStyle['roundness'] ?? 'default',
                        'density' => $defStyle['density'] ?? 'comfortable',
                        'custom_css' => $defCustom['css'] ?? '',
                        'custom_js' => $defCustom['js'] ?? '',
                    ]);

                    Notification::make()
                        ->title('Przywrócono domyślne ustawienia')
                        ->info()
                        ->send();
                }),
        ];
    }

    public function save(): void
    {
        /** @var array<string, mixed> $data */
        $data = $this->form->getState();

        // Validate colors
        $primaryColor = is_string($data['primary_color'] ?? null) ? $data['primary_color'] : '#3b82f6';
        $accentColor = is_string($data['accent_color'] ?? null) ? $data['accent_color'] : '#10b981';

        if (! $this->themeService->isValidHex($primaryColor)) {
            Notification::make()
                ->title('Nieprawidłowy format koloru głównego')
                ->danger()
                ->send();

            return;
        }

        if (! $this->themeService->isValidHex($accentColor)) {
            Notification::make()
                ->title('Nieprawidłowy format koloru akcentowego')
                ->danger()
                ->send();

            return;
        }

        // Build config structure
        $config = [
            'base_theme' => is_string($data['base_theme'] ?? null) ? $data['base_theme'] : 'bitfloo-dark',
            'mode' => is_string($data['mode'] ?? null) ? $data['mode'] : 'dark',
            'colors' => [
                'primary' => $primaryColor,
                'accent' => $accentColor,
            ],
            'style' => [
                'roundness' => is_string($data['roundness'] ?? null) ? $data['roundness'] : 'default',
                'density' => is_string($data['density'] ?? null) ? $data['density'] : 'comfortable',
            ],
            'custom' => [
                'css' => is_string($data['custom_css'] ?? null) ? $data['custom_css'] : '',
                'js' => is_string($data['custom_js'] ?? null) ? $data['custom_js'] : '',
            ],
        ];

        $this->themeService->saveConfig($config);

        // Check contrast for warning
        $contrastOk = $this->themeService->meetsWcagAA('#ffffff', $primaryColor);

        if ($contrastOk) {
            Notification::make()
                ->title('Ustawienia zapisane')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Ustawienia zapisane')
                ->body('Uwaga: kolor główny może mieć niski kontrast z białym tekstem.')
                ->warning()
                ->send();
        }
    }

    /**
     * Get current colors for Alpine.js preview.
     *
     * @return array<string, string>
     */
    public function getPreviewColors(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->form->getState();

        return [
            'primary' => is_string($data['primary_color'] ?? null) ? $data['primary_color'] : '#3b82f6',
            'accent' => is_string($data['accent_color'] ?? null) ? $data['accent_color'] : '#10b981',
        ];
    }
}
