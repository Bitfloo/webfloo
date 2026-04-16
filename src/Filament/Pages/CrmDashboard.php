<?php

declare(strict_types=1);

namespace Webfloo\Filament\Pages;

use BackedEnum;
use Webfloo\Filament\Resources\LeadResource;
use Webfloo\Mail\LeadEmail;
use Webfloo\Models\Lead;
use Webfloo\Models\LeadActivity;
use Webfloo\Models\LeadReminder;
use Webfloo\Models\LeadTag;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use UnitEnum;

class CrmDashboard extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::RectangleGroup;

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = -1;

    protected static ?string $navigationLabel = 'CRM';

    protected static ?string $title = 'L.E.A.D.S';

    protected static ?string $slug = 'crm';

    protected string $view = 'webfloo::filament.pages.crm-dashboard';

    public static function canAccess(): bool
    {
        if (! config('webfloo.features.crm', true)) {
            return false;
        }

        return auth()->user()?->can('view_crm_dashboard') === true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public string $viewMode = 'kanban';

    public string $searchQuery = '';

    public ?int $selectedLeadId = null;

    public function getTitle(): string|Htmlable
    {
        return 'L.E.A.D.S';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Lead Engagement And Development System';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggleView')
                ->label(fn () => $this->viewMode === 'kanban' ? 'Lista' : 'Kanban')
                ->icon(fn () => $this->viewMode === 'kanban' ? Heroicon::Bars3 : Heroicon::ViewColumns)
                ->color('gray')
                ->action(fn () => $this->viewMode = $this->viewMode === 'kanban' ? 'list' : 'kanban'),

            Action::make('exportLeads')
                ->label('Eksport')
                ->icon(Heroicon::ArrowDownTray)
                ->color('gray')
                ->url(LeadResource::getUrl('index', ['tableAction' => 'export'])),

            $this->createLeadAction(),
        ];
    }

    public function createLeadAction(): Action
    {
        return Action::make('createLead')
            ->label('Nowy Lead')
            ->icon(Heroicon::Plus)
            ->modalHeading('Dodaj nowego leada')
            ->modalWidth('lg')
            ->form([
                Grid::make(2)->schema([
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

                    Select::make('source')
                        ->label('Źródło')
                        ->options(Lead::getSourceOptions())
                        ->default(Lead::SOURCE_MANUAL)
                        ->native(false),

                    TextInput::make('estimated_value')
                        ->label('Szacowana wartość')
                        ->numeric()
                        ->prefix('PLN'),
                ]),

                Textarea::make('message')
                    ->label('Notatka')
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                $lead = Lead::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'company' => $data['company'] ?? null,
                    'message' => $data['message'] ?? null,
                    'source' => $data['source'],
                    'status' => Lead::STATUS_NEW,
                    'estimated_value' => $data['estimated_value'] ?? null,
                    'currency' => 'PLN',
                ]);

                $lead->activities()->create([
                    'type' => LeadActivity::TYPE_CREATED,
                    'title' => 'Lead utworzony',
                    'user_id' => auth()->id(),
                ]);

                Notification::make()
                    ->title('Lead dodany!')
                    ->success()
                    ->send();
            });
    }

    public function editLeadAction(): Action
    {
        return Action::make('editLead')
            ->modalHeading('Edytuj leada')
            ->modalWidth('lg')
            ->form(fn () => $this->getLeadFormSchema())
            ->fillForm(function (): array {
                $lead = Lead::find($this->selectedLeadId);

                return $lead ? $lead->toArray() : [];
            })
            ->action(function (array $data): void {
                $lead = Lead::findOrFail($this->selectedLeadId);
                /** @var array<string, mixed> $data */
                $lead->update($data);

                Notification::make()
                    ->title('Lead zaktualizowany')
                    ->success()
                    ->send();
            });
    }

    public function addNoteAction(): Action
    {
        return Action::make('addNote')
            ->modalHeading('Dodaj notatkę')
            ->form([
                Textarea::make('note')
                    ->label('Notatka')
                    ->required()
                    ->rows(4),
            ])
            ->action(function (array $data): void {
                $lead = Lead::findOrFail($this->selectedLeadId);
                $note = $data['note'] ?? '';
                $lead->addNote(is_string($note) ? $note : '');

                Notification::make()
                    ->title('Notatka dodana')
                    ->success()
                    ->send();
            });
    }

    public function logCallAction(): Action
    {
        return Action::make('logCall')
            ->modalHeading('Zaloguj rozmowę')
            ->form([
                Textarea::make('notes')
                    ->label('Notatki z rozmowy')
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                $lead = Lead::findOrFail($this->selectedLeadId);
                $notes = isset($data['notes']) && is_string($data['notes']) && $data['notes'] !== '' ? $data['notes'] : null;
                $lead->logCall($notes);

                Notification::make()
                    ->title('Rozmowa zalogowana')
                    ->success()
                    ->send();
            });
    }

    public function sendEmailAction(): Action
    {
        return Action::make('sendEmail')
            ->modalHeading('Wyślij email')
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
            ->action(function (array $data): void {
                $lead = Lead::findOrFail($this->selectedLeadId);
                /** @var string $subject */
                $subject = $data['subject'];
                /** @var string $body */
                $body = $data['body'];

                Mail::to($lead->email)->send(new LeadEmail($lead, $subject, $body));
                $lead->logEmail($subject, $body);

                Notification::make()
                    ->title('Email wysłany')
                    ->success()
                    ->send();
            });
    }

    public function scheduleReminderAction(): Action
    {
        return Action::make('scheduleReminder')
            ->modalHeading('Zaplanuj przypomnienie')
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
            ])
            ->action(function (array $data): void {
                $lead = Lead::findOrFail($this->selectedLeadId);
                /** @var string $title */
                $title = $data['title'];
                /** @var \DateTimeInterface $dueAt */
                $dueAt = $data['due_at'];
                /** @var string $priority */
                $priority = $data['priority'];
                $lead->scheduleReminder($title, $dueAt, null, $priority);

                Notification::make()
                    ->title('Przypomnienie zaplanowane')
                    ->success()
                    ->send();
            });
    }

    /**
     * @return array<int, mixed>
     */
    protected function getLeadFormSchema(): array
    {
        return [
            Grid::make(2)->schema([
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

                Select::make('status')
                    ->label('Status')
                    ->options(Lead::getStatusOptions())
                    ->native(false),

                Select::make('source')
                    ->label('Źródło')
                    ->options(Lead::getSourceOptions())
                    ->native(false),

                Select::make('assigned_to')
                    ->label('Przypisany do')
                    ->options(function (): array {
                        $userModel = webfloo_user_model();

                        /** @var array<int|string, string> */
                        return $userModel::query()->pluck('name', 'id')->all();
                    })
                    ->searchable()
                    ->native(false),

                TextInput::make('estimated_value')
                    ->label('Szacowana wartość')
                    ->numeric()
                    ->prefix('PLN'),

                Select::make('tags')
                    ->label('Tagi')
                    ->multiple()
                    ->options(LeadTag::pluck('name', 'id'))
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nazwa tagu')
                            ->required()
                            ->maxLength(50),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        $tag = LeadTag::create(['name' => $data['name'], 'color' => 'gray']);

                        return $tag->id;
                    }),
            ]),

            Textarea::make('message')
                ->label('Wiadomość')
                ->rows(3),
        ];
    }

    // ==================== KANBAN DATA ====================

    /**
     * @return array<array{id: string, title: string, color: string}>
     */
    public function getKanbanColumns(): array
    {
        return [
            ['id' => Lead::STATUS_NEW, 'title' => 'Nowy', 'color' => 'warning'],
            ['id' => Lead::STATUS_CONTACTED, 'title' => 'Skontaktowany', 'color' => 'info'],
            ['id' => Lead::STATUS_QUALIFIED, 'title' => 'Zakwalifikowany', 'color' => 'primary'],
        ];
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getKanbanLeads(): array
    {
        $query = Lead::inPipeline()
            ->with(['assignee', 'pendingReminders', 'tags'])
            ->orderByDesc('created_at');

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->searchQuery}%")
                    ->orWhere('email', 'like', "%{$this->searchQuery}%")
                    ->orWhere('company', 'like', "%{$this->searchQuery}%");
            });
        }

        $leads = $query->get();

        $grouped = [];
        foreach (Lead::PIPELINE_STATUSES as $status) {
            $grouped[$status] = [];
        }

        foreach ($leads as $lead) {
            $grouped[$lead->status][] = [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'company' => $lead->company,
                'message' => $lead->message,
                'estimated_value' => $lead->estimated_value,
                'currency' => $lead->currency,
                'source' => $lead->source,
                'created_at' => $lead->created_at->format('d.m.Y'),
                'assignee' => $lead->assignee ? ['name' => $lead->assignee->name] : null,
                'pending_reminders_count' => $lead->pendingReminders->count(),
                'tags' => $lead->tags->map(fn ($t) => ['name' => $t->name, 'color' => $t->color])->toArray(),
            ];
        }

        return $grouped;
    }

    /**
     * @return array<string, int|float>
     */
    public function getStats(): array
    {
        return [
            'total' => Lead::count(),
            'new' => Lead::where('status', Lead::STATUS_NEW)->count(),
            'pipeline' => Lead::inPipeline()->count(),
            'converted' => Lead::converted()->count(),
            'lost' => Lead::lost()->count(),
            'pipeline_value' => (float) Lead::inPipeline()->sum('estimated_value'),
            'conversion_rate' => $this->calculateConversionRate(),
        ];
    }

    protected function calculateConversionRate(): float
    {
        $total = Lead::whereIn('status', [Lead::STATUS_CONVERTED, Lead::STATUS_LOST])->count();
        if ($total === 0) {
            return 0;
        }

        $converted = Lead::converted()->count();

        return round(($converted / $total) * 100, 1);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecentReminders(): array
    {
        /** @var array<int, array<string, mixed>> $reminders */
        $reminders = LeadReminder::query()
            ->pending()
            ->with(['lead'])
            ->orderBy('due_at')
            ->limit(5)
            ->get()
            ->map(fn (LeadReminder $r): array => [
                'id' => $r->id,
                'title' => $r->title,
                'lead_name' => $r->lead->name,
                'lead_id' => $r->lead_id,
                'due_at' => $r->due_at->format('d.m H:i'),
                'is_overdue' => $r->isOverdue(),
                'priority' => $r->priority,
            ])
            ->toArray();

        return $reminders;
    }

    // ==================== ACTIONS ====================

    public function openLeadModal(int $leadId, string $action): void
    {
        $this->selectedLeadId = $leadId;
        $this->mountAction($action);
    }

    #[On('lead-moved')]
    public function moveLeadToStatus(int $leadId, string $newStatus): void
    {
        $lead = Lead::findOrFail($leadId);
        $lead->transitionTo($newStatus);

        Notification::make()
            ->title('Status zaktualizowany')
            ->success()
            ->send();
    }

    #[On('lead-converted')]
    public function convertLead(int $leadId): void
    {
        $lead = Lead::findOrFail($leadId);
        $lead->markAsConverted();

        Notification::make()
            ->title('Lead skonwertowany!')
            ->success()
            ->send();
    }

    #[On('lead-lost')]
    public function markLeadAsLost(int $leadId): void
    {
        $lead = Lead::findOrFail($leadId);
        $lead->markAsLost();

        Notification::make()
            ->title('Lead oznaczony jako utracony')
            ->warning()
            ->send();
    }

    public function updatedSearchQuery(): void
    {
        // Livewire will automatically re-render
    }
}
