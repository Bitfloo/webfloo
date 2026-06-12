# Filament v5 API Reference (SSOT)

Single source of truth for Filament v5 imports and patterns used in webfloo. `CLAUDE.md` ("Adding a Filament Resource", "Rules") and `docs/ARCHITECTURE.md` (SSOT section) reference this file. Consumers (bitfloo-web, future clients) reference it too — do not duplicate.

Version context: webfloo requires `filament/filament: ^5.0` (see `docs/decisions/005-webfloo-host-contract.md`). Filament v5 has the same API as v4 — the major bump exists for Livewire v4 support. The dangerous patterns are **v3-era** habits, listed at the bottom.

## Imports — where things live in v5

The v3 → v4/v5 reorganization split components across namespaces. Every import below is in active use under `src/Filament/`.

### Schemas (layout + the unified Schema object)

```php
use Filament\Schemas\Schema;                       // unified form/infolist schema
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Utilities\Get;     // NOT Filament\Forms\Get
use Filament\Schemas\Components\Utilities\Set;     // NOT Filament\Forms\Set
```

### Form fields (input components stay in Forms)

```php
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;    // custom Pages with forms
use Filament\Forms\Contracts\HasForms;
```

### Actions (ONE namespace for table/page/bulk actions)

```php
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Concerns\InteractsWithActions; // custom Pages with actions
use Filament\Actions\Contracts\HasActions;
```

There is no `Filament\Tables\Actions\*` anymore — table actions import from `Filament\Actions\*`.

### Tables

```php
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
```

### Resources, Pages, Widgets, misc

```php
use Filament\Resources\Resource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Pages\Page;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\TableWidget;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;               // icon enum
```

## Canonical Resource skeleton

Condensed from `src/Filament/Resources/FaqResource.php` — the reference implementation:

```php
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    // BackedEnum union type + Heroicon enum, NOT a 'heroicon-o-...' string
    protected static string|BackedEnum|null $navigationIcon = Heroicon::QuestionMarkCircle;

    // Feature flag + spatie/laravel-permission gate (webfloo convention)
    public static function canAccess(): bool
    {
        if (! config('webfloo.features.faq', true)) {
            return false;
        }

        return auth()->user()?->can('view_any_faq') === true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    // Unified Schema — NOT form(Form $form): Form
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('...'))->schema([
                TextInput::make('question')->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([...])
            ->filters([...])
            ->recordActions([        // NOT ->actions()
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([       // NOT ->bulkActions()
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

## Custom Page skeleton

From `src/Filament/Pages/PageSettings/AbstractPageSettings.php`:

```php
abstract class AbstractPageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    // NON-static in v5 — `protected static string $view` is a fatal error
    protected string $view = 'webfloo::filament.pages.settings-page';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Swatch;
}
```

## v3 habits that break v5 (AI assistants generate these constantly)

| v3 habit (WRONG) | v5 (correct) |
|---|---|
| `form(Form $form): Form` | `form(Schema $schema): Schema` |
| `Filament\Forms\Components\{Grid,Section,Tabs,Fieldset}` | `Filament\Schemas\Components\*` (fields stay in `Forms\Components`) |
| `Filament\Forms\{Get,Set}` | `Filament\Schemas\Components\Utilities\{Get,Set}` |
| `Filament\Tables\Actions\EditAction` | `Filament\Actions\EditAction` |
| `->actions([...])` / `->bulkActions([...])` on Table | `->recordActions([...])` / `->toolbarActions([...])` |
| `->form([...])` on an Action or Filter | `->schema([...])` |
| `->mutateFormDataUsing()` | `->mutateDataUsing()` |
| `$navigationIcon = 'heroicon-o-...'` (plain string typed `?string`) | `string\|BackedEnum\|null` + `Heroicon::*` enum case |
| `protected static string $view` on a Page | `protected string $view` (non-static; static is a fatal "cannot redeclare" error) |
| `Filament\Schemas\Components\TextEntry` (hallucinated) | `Filament\Infolists\Components\TextEntry` |
| `route('filament...')` for resource URLs | `FaqResource::getUrl('index')` |

When unsure about an API, read an existing Resource in `src/Filament/Resources/` first — live code is authoritative over memory.
