<?php

namespace Webfloo\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Webfloo\Filament\Resources\NewsletterSubscriberResource\Pages\CreateNewsletterSubscriber;
use Webfloo\Filament\Resources\NewsletterSubscriberResource\Pages\EditNewsletterSubscriber;
use Webfloo\Filament\Resources\NewsletterSubscriberResource\Pages\ListNewsletterSubscribers;
use Webfloo\Models\NewsletterSubscriber;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Envelope;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('CRM');
    }

    public static function getModelLabel(): string
    {
        return __('Subskrybent');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Newsletter');
    }

    public static function canAccess(): bool
    {
        // PII scope (GDPR): subscriber emails, IPs, names. admin-only
        // by default via ShieldRolesSeeder — editor role does NOT hold
        // `view_any_newsletter_subscriber`. Do not widen without legal review.
        if (! config('webfloo.features.newsletter', true)) {
            return false;
        }

        return auth()->user()?->can('view_any_newsletter_subscriber') === true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dane subskrybenta')
                ->columns(2)
                ->schema([
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('name')
                        ->label('Imię')
                        ->maxLength(100),

                    Toggle::make('is_active')
                        ->label('Aktywny')
                        ->default(true),

                    TextInput::make('source')
                        ->label('Źródło')
                        ->default('footer')
                        ->maxLength(50),
                ]),

            Section::make('Daty')
                ->columns(2)
                ->collapsed()
                ->schema([
                    DateTimePicker::make('subscribed_at')
                        ->label('Data zapisania')
                        ->default(now()),

                    DateTimePicker::make('unsubscribed_at')
                        ->label('Data wypisania'),

                    TextInput::make('ip_address')
                        ->label('Adres IP')
                        ->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('name')
                    ->label('Imię')
                    ->searchable()
                    ->placeholder('—'),

                IconColumn::make('is_active')
                    ->label('Aktywny')
                    ->boolean(),

                TextColumn::make('source')
                    ->label('Źródło')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('subscribed_at')
                    ->label('Data zapisania')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('subscribed_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktywni')
                    ->falseLabel('Nieaktywni')
                    ->placeholder('Wszyscy'),

                SelectFilter::make('source')
                    ->label('Źródło')
                    ->options([
                        'footer' => 'Stopka',
                        'blog' => 'Blog',
                        'popup' => 'Popup',
                        'landing' => 'Landing',
                        'manual' => 'Ręczny',
                    ]),
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
            'index' => ListNewsletterSubscribers::route('/'),
            'create' => CreateNewsletterSubscriber::route('/create'),
            'edit' => EditNewsletterSubscriber::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) NewsletterSubscriber::active()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
