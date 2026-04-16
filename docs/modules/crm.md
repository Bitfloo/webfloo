# Module: CRM (Leads)

**Feature flag:** `webfloo.features.crm` (default `true`)
**Scope:** Lead pipeline (new → contacted → qualified → converted/lost) + tag taxonomy + activities timeline + reminders + kanban dashboard + CSV export + public API webhook. **Największy moduł** — PII sensitive (lead emails, phone, business data).

## Public API

### Resources
- `Webfloo\Filament\Resources\LeadResource` — main pipeline CRUD
- `Webfloo\Filament\Resources\LeadTagResource` — tag taxonomy

### Pages (non-Resource)
- `Webfloo\Filament\Pages\CrmDashboard` — custom kanban view with 5 widgets inline

### Widgets
- `LeadStatsOverview` — pipeline counts + conversion rate
- `LeadConversionChart` — 6-month conversion trend
- `LeadsByStatusChart` — status distribution
- `LeadsBySourceChart` — source distribution
- `UpcomingRemindersWidget` — due date reminders

### Models
- `Lead` — core entity (name, email, phone, company, status, source, assigned_to, estimated_value, currency, consent_at, metadata JSON, external_id)
- `LeadTag` — taxonomy (name, color)
- `LeadActivity` — timeline event (type enum, title, description, user_id, metadata)
- `LeadReminder` — task (title, description, due_at, completed_at, priority enum, notification_sent)

### Exports
- `Webfloo\Filament\Exports\LeadExporter` — CSV export z polskim datetime format

### Controllers (public API)
- `Webfloo\Http\Controllers\Api\LeadWebhookController` — POST `/api/leads/webhook` z HMAC auth (`hash_equals`)

### Mail
- `Webfloo\Mail\LeadEmail` — notification dla lead
- `Webfloo\Mail\NewLeadNotification` — alert dla admin

### Commands
- `Webfloo\Console\Commands\SendLeadReminders` — cron task (due reminders → email)

## Migrations

- `*_create_leads_table`
- `*_create_lead_activities_table`
- `*_create_lead_reminders_table`
- `*_create_lead_tags_table`
- `*_add_crm_fields_to_leads_table` — assigned_to, estimated_value, metadata
- `*_add_consent_at_to_leads_table` — GDPR consent timestamp
- `*_add_converted_at_index_to_leads_table` — perf index (Phase 1.5q)

## Shield permissions

```
view_any_lead       view_lead       create_lead       update_lead       delete_lead
view_any_lead_tag   view_lead_tag   create_lead_tag   update_lead_tag   delete_lead_tag
view_crm_dashboard
```

Role assignments:
- `super_admin` — all
- `editor` — full CRUD (editor manages sales pipeline)
- `viewer` — view_any + view only (read-only sales visibility)

## Host integration

### API webhook

**Endpoint:** `POST /api/leads/webhook` (middleware: `api` + throttle `30,1`)

**Headers:**
```
X-Webhook-Secret: <value from BITFLOO_WEBHOOK_SECRET env>
Content-Type: application/json
```

**Payload:**
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "phone": "+48123456789",
  "company": "ACME",
  "message": "...",
  "source": "contact_form",
  "consent_at": "2026-04-17T10:00:00Z",
  "external_id": "ext-uuid"
}
```

**Security:**
- `hash_equals()` timing-safe comparison dla `X-Webhook-Secret`.
- Throttle 30 requests / minute / IP.
- Input validated przez `Validator::make` z whitelist enum dla `source` + `status`.

**Update endpoint:** `PATCH /api/leads/webhook/{externalId}` — update pola by external_id.

### Cron task

```bash
# crontab
* * * * * cd /var/www && php artisan webfloo:send-lead-reminders >> /dev/null 2>&1
```

Rejestrowany automatycznie gdy `features.crm = true`. Command filtruje reminders z `due_at <= now()` + `completed_at = null` + `notification_sent = false`.

### Feature flag scenarios

- `features.crm = false`:
  - Lead + LeadTag Resources niewidoczne.
  - CrmDashboard niewidoczny.
  - 5 widgetów nie rejestrowane.
  - API webhook route **nie wire'owany** w `WebflooServiceProvider::registerRoutes()` → 404 dla POST `/api/leads/webhook`.
  - `SendLeadReminders` command **nie rejestrowany** (via `ModuleRegistry::enabledCommands()`) — cron job nie znajdzie komendy.
  - Lead data w DB zostaje (safe disable — re-enable restoruje pełny panel).

## Testing

Manual smoke test:
1. `POST /api/leads/webhook` z poprawnym `X-Webhook-Secret` → 201 + Lead created.
2. `POST /api/leads/webhook` bez secret → 401.
3. Navigate `/admin/crm` → kanban z 3 kolumnami (new / contacted / qualified).
4. Create reminder dla Lead z `due_at = now()` → `php artisan webfloo:send-lead-reminders` → email wysłany + `notification_sent = true` + activity log created.

## Limitations / known gaps

- **Kanban unbounded `->get()` na `getKanbanLeads()`** = D1 deferred (alpha → beta). Scope ograniczony przez `inPipeline()` = 3 statuses, praktyczny cap.
- **Currency hardcoded PLN** w migration + controller (post-push-gate SSOT finding). Host follow-up: `Lead::DEFAULT_CURRENCY` constant albo `config('webfloo.default_currency')`.
- **Webhook secret env name:** `BITFLOO_WEBHOOK_SECRET` (leftover po rename) — follow-up: rename do `WEBFLOO_WEBHOOK_SECRET`.
- Lead scoring nie zaimplementowane (future feature).
- Automated email follow-up nie zaimplementowane (cron tylko dla reminders).
