<?php

declare(strict_types=1);

namespace Webfloo\Filament\Resources;

use BackedEnum;
use Webfloo\Filament\Exports\LeadExporter;
use Webfloo\Filament\Resources\LeadResource\Pages;
use Webfloo\Filament\Resources\LeadResource\RelationManagers;
use Webfloo\Mail\LeadEmail;
use Webfloo\Models\Lead;
use Webfloo\Models\LeadReminder;
use Webfloo\Models\LeadTag;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('CRM');
    }

    public static function getNavigationLabel(): string
    {
        return __('Leady');
    }

    public static function getModelLabel(): string
    {
        return __('Lead');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Leady');
    }

    public static function canAccess(): bool
    {
        if (! config('webfloo.features.crm', true)) {
            return false;
        }

        return auth()->user()?->can('view_any_lead') === true;
    }

    // Ukryj z nawigacji -- dostep tylko przez CRM Dashboard
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dane kontaktowe')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Imię i nazwisko')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->maxLength(255),

                    TextInput::make('phone')
                        ->label('Telefon')
                        ->tel()
                        ->maxLength(20),

                    TextInput::make('company')
                        ->label('Firma')
                        ->maxLength(100),
                ]),

            Section::make('Wiadomość')
                ->schema([
                    Textarea::make('message')
                        ->label('Treść wiadomości')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),

            Section::make('Status i wartość')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options(Lead::getStatusOptions())
                        ->default(Lead::STATUS_NEW)
                        ->native(false),

                    Select::make('source')
                        ->label('Źródło')
                        ->options(Lead::getSourceOptions())
                        ->default(Lead::SOURCE_CONTACT_FORM)
                        ->native(false),

                    Select::make('assigned_to')
                        ->label('Przypisany do')
                        ->relationship('assignee', 'name')
                        ->searchable()
                        ->preload()
                        ->native(false),

                    Select::make('tags')
                        ->label('Tagi')
                        ->relationship('tags', 'name')
                        ->multiple()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('Nazwa')
                                ->required()
                                ->maxLength(50),
                            Select::make('color')
                                ->label('Kolor')
                                ->options(LeadTag::getColorOptions())
                                ->default('gray')
                                ->native(false),
                        ]),

                    TextInput::make('estimated_value')
                        ->label('Szacowana wartość')
                        ->numeric()
                        ->prefix('PLN')
                        ->step(0.01),

                    Select::make('currency')
                        ->label('Waluta')
                        ->options([
                            'PLN' => 'PLN',
                            'EUR' => 'EUR',
                            'USD' => 'USD',
                        ])
                        ->default('PLN')
                        ->native(false),
                ]),

            Section::make('Notatki')
                ->collapsed()
                ->schema([
                    Textarea::make('notes')
                        ->label('Notatki wewnętrzne')
                        ->rows(3)
                        ->columnSpanFull(),

                    KeyValue::make('metadata')
                        ->label('Dodatkowe dane')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['assignee', 'tags']))
            ->columns([
                TextColumn::make('name')
                    ->label('Imię i nazwisko')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Lead $record): ?string => $record->company),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Lead::getStatusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => Lead::getStatusColors()[$state] ?? 'gray'),

                TextColumn::make('source')
                    ->label('Źródło')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Lead::getSourceOptions()[$state] ?? $state)
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('estimated_value')
                    ->label('Wartość')
                    ->money('PLN')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('assignee.name')
                    ->label('Przypisany')
                    ->toggleable()
                    ->placeholder('Nieprzypisany'),

                TextColumn::make('tags.name')
                    ->label('Tagi')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('last_contacted_at')
                    ->label('Ostatni kontakt')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Lead::getStatusOptions())
                    ->multiple(),

                SelectFilter::make('source')
                    ->label('Źródło')
                    ->options(Lead::getSourceOptions())
                    ->multiple(),

                SelectFilter::make('assigned_to')
                    ->label('Przypisany do')
                    ->relationship('assignee', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('unassigned')
                    ->label('Nieprzypisane')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('assigned_to'),
                        false: fn (Builder $query) => $query->whereNotNull('assigned_to'),
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                    // Quick Actions
                    Action::make('addNote')
                        ->label('Dodaj notatkę')
                        ->icon(Heroicon::DocumentText)
                        ->color('gray')
                        ->form([
                            Textarea::make('note')
                                ->label('Notatka')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function (Lead $record, array $data): void {
                            /** @var string $note */
                            $note = $data['note'];
                            $record->addNote($note);
                            Notification::make()
                                ->title('Notatka dodana')
                                ->success()
                                ->send();
                        }),

                    Action::make('logCall')
                        ->label('Zaloguj rozmowę')
                        ->icon(Heroicon::Phone)
                        ->color('gray')
                        ->form([
                            Textarea::make('notes')
                                ->label('Notatki z rozmowy')
                                ->rows(3),
                        ])
                        ->action(function (Lead $record, array $data): void {
                            $notes = isset($data['notes']) && is_string($data['notes']) ? $data['notes'] : null;
                            $record->logCall($notes);
                            Notification::make()
                                ->title('Rozmowa zalogowana')
                                ->success()
                                ->send();
                        }),

                    Action::make('sendEmail')
                        ->label('Wyślij email')
                        ->icon(Heroicon::Envelope)
                        ->color('gray')
                        ->visible(fn (Lead $record) => ! empty($record->email))
                        ->form([
                            TextInput::make('subject')
                                ->label('Temat')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('body')
                                ->label('Treść')
                                ->required()
                                ->rows(6),
                        ])
                        ->action(function (Lead $record, array $data): void {
                            /** @var string $subject */
                            $subject = $data['subject'];
                            /** @var string $body */
                            $body = $data['body'];

                            Mail::to($record->email)->send(new LeadEmail($record, $subject, $body));
                            $record->logEmail($subject, $body);

                            Notification::make()
                                ->title('Email wysłany')
                                ->success()
                                ->send();
                        }),

                    Action::make('scheduleReminder')
                        ->label('Zaplanuj przypomnienie')
                        ->icon(Heroicon::Bell)
                        ->color('gray')
                        ->form([
                            TextInput::make('title')
                                ->label('Tytuł')
                                ->required()
                                ->maxLength(100),
                            DateTimePicker::make('due_at')
                                ->label('Termin')
                                ->required()
                                ->minDate(now())
                                ->native(false),
                            Select::make('priority')
                                ->label('Priorytet')
                                ->options(LeadReminder::getPriorityOptions())
                                ->default(LeadReminder::PRIORITY_NORMAL)
                                ->native(false),
                            Textarea::make('description')
                                ->label('Opis')
                                ->rows(2),
                        ])
                        ->action(function (Lead $record, array $data): void {
                            /** @var string $title */
                            $title = $data['title'];
                            /** @var \DateTimeInterface $dueAt */
                            $dueAt = $data['due_at'];
                            /** @var string|null $description */
                            $description = $data['description'] ?? null;
                            /** @var string $priority */
                            $priority = $data['priority'];
                            $record->scheduleReminder($title, $dueAt, $description, $priority);
                            Notification::make()
                                ->title('Przypomnienie zaplanowane')
                                ->success()
                                ->send();
                        }),

                    // Status transitions
                    Action::make('markContacted')
                        ->label('Oznacz jako skontaktowany')
                        ->icon(Heroicon::ChatBubbleLeftRight)
                        ->color('info')
                        ->visible(fn (Lead $record) => $record->status === Lead::STATUS_NEW)
                        ->requiresConfirmation()
                        ->action(function (Lead $record): void {
                            $record->markAsContacted();
                            Notification::make()
                                ->title('Status zmieniony')
                                ->success()
                                ->send();
                        }),

                    Action::make('markQualified')
                        ->label('Zakwalifikuj')
                        ->icon(Heroicon::CheckBadge)
                        ->color('primary')
                        ->visible(fn (Lead $record) => $record->status === Lead::STATUS_CONTACTED)
                        ->requiresConfirmation()
                        ->action(function (Lead $record): void {
                            $record->markAsQualified();
                            Notification::make()
                                ->title('Lead zakwalifikowany')
                                ->success()
                                ->send();
                        }),

                    Action::make('convert')
                        ->label('Konwertuj')
                        ->icon(Heroicon::Trophy)
                        ->color('success')
                        ->visible(fn (Lead $record) => $record->isInPipeline())
                        ->requiresConfirmation()
                        ->modalHeading('Konwertuj leada')
                        ->modalDescription('Czy na pewno chcesz oznaczyć tego leada jako skonwertowanego?')
                        ->action(function (Lead $record): void {
                            $record->markAsConverted();
                            Notification::make()
                                ->title('Lead skonwertowany!')
                                ->success()
                                ->send();
                        }),

                    Action::make('markLost')
                        ->label('Oznacz jako utracony')
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->visible(fn (Lead $record) => $record->isInPipeline())
                        ->requiresConfirmation()
                        ->modalHeading('Oznacz jako utracony')
                        ->modalDescription('Czy na pewno chcesz oznaczyć tego leada jako utraconego?')
                        ->action(function (Lead $record): void {
                            $record->markAsLost();
                            Notification::make()
                                ->title('Lead oznaczony jako utracony')
                                ->warning()
                                ->send();
                        }),

                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Eksportuj')
                    ->exporter(LeadExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ActivitiesRelationManager::class,
            RelationManagers\RemindersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'view' => Pages\ViewLead::route('/{record}'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', Lead::STATUS_NEW)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
