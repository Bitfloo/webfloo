# Testing Standards — webfloo

> SSOT for test quality in this package. Read this before writing or reviewing any test. Applies to Laravel 12 + Filament v5 + PHPUnit 11/12 + spatie/laravel-translatable v6 + Orchestra Testbench.

## Test taxonomy

| Suite | Path | Purpose | Base |
|---|---|---|---|
| **Unit** | `tests/Unit/` | Pure PHP — traits, value objects, scope bodies | `Webfloo\Tests\TestCase` |
| **Feature** | `tests/Feature/` | Integration — DB, Livewire, Filament Resources | `Webfloo\Tests\TestCase` |
| **Feature/Filament** | `tests/Feature/Filament/` | Filament Resource interactions (CRUD, table, policies) | `Webfloo\Tests\TestCase` |

**Always extend `Webfloo\Tests\TestCase`** (Orchestra Testbench — this is a package, not an app). Never use `Tests\TestCase` or `Illuminate\Foundation\Testing\TestCase`.

## Bootstrap

Environment (`defineEnvironment`) already provides in-memory sqlite. To enable migrations:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class XxxTest extends TestCase
{
    use RefreshDatabase;
    // ...
}
```

In package context you must load migrations yourself:

```php
protected function defineDatabaseMigrations(): void
{
    $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
}
```

Add in TestCase if missing, or per-test override.

## DO ✓

### 1. `RefreshDatabase` — real migrations, real DB, real queries

```php
use RefreshDatabase;
```

Real SQL runs. Models get saved. Relations resolve. Catches: broken migrations, broken relationships, wrong cast definitions, missing `$fillable`, broken JSON translatable storage.

### 2. Test policies & authorization — not just happy path

```php
public function test_unauthorized_user_cannot_access_resource(): void
{
    $user = User::factory()->create(); // no permission
    $this->actingAs($user)
        ->get(FaqResource::getUrl('index'))
        ->assertForbidden();
}

public function test_authorized_user_can_access(): void
{
    $admin = User::factory()->create();
    $admin->givePermissionTo('view_any_faq'); // spatie/laravel-permission
    $this->actingAs($admin)
        ->get(FaqResource::getUrl('index'))
        ->assertSuccessful();
}
```

### 3. Test Filament Resource interactions via Livewire

Filament Resource pages ARE Livewire components. Use `Livewire::test()`:

```php
use Livewire\Livewire;
use Webfloo\Filament\Resources\FaqResource\Pages\CreateFaq;

public function test_create_faq_saves_translatable_fields(): void
{
    $admin = $this->makeAdmin();
    $this->actingAs($admin);

    Livewire::test(CreateFaq::class)
        ->fillForm([
            'question' => ['pl' => 'Pytanie?', 'en' => 'Question?'],
            'answer' => ['pl' => 'Odpowiedź', 'en' => 'Answer'],
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $faq = Faq::latest('id')->first();
    $this->assertSame('Pytanie?', $faq->getTranslation('question', 'pl'));
    $this->assertSame('Question?', $faq->getTranslation('question', 'en'));
}
```

### 4. Translatable JSON fields — test BOTH locales separately

```php
public function test_translatable_field_stores_both_locales(): void
{
    $faq = Faq::factory()->create();
    $faq->setTranslation('question', 'pl', 'Cześć?');
    $faq->setTranslation('question', 'en', 'Hello?');
    $faq->save();

    $faq->refresh();
    $this->assertSame('Cześć?', $faq->getTranslation('question', 'pl'));
    $this->assertSame('Hello?', $faq->getTranslation('question', 'en'));

    // Raw JSON structure check — detects silent serialization bugs
    $raw = DB::table('faqs')->where('id', $faq->id)->value('question');
    $this->assertJson($raw);
    $decoded = json_decode($raw, true);
    $this->assertArrayHasKey('pl', $decoded);
    $this->assertArrayHasKey('en', $decoded);
}

public function test_falls_back_when_locale_missing(): void
{
    $faq = Faq::factory()->create();
    $faq->setTranslation('question', 'pl', 'Tylko PL');
    $faq->save();

    // Depending on spatie config: fallback locale returns null or fallback value
    $this->assertNull($faq->getTranslation('question', 'en', false));
}
```

### 5. Table actions — assert existence AND behavior

```php
use Webfloo\Filament\Resources\FaqResource\Pages\ListFaqs;

public function test_table_has_delete_action(): void
{
    $faq = Faq::factory()->create();
    $this->actingAs($this->makeAdmin());

    Livewire::test(ListFaqs::class)
        ->assertTableActionExists('delete')
        ->callTableAction('delete', $faq)
        ->assertOk();

    $this->assertSoftDeleted($faq);
}

public function test_bulk_action_deletes_multiple(): void
{
    $faqs = Faq::factory()->count(3)->create();
    $this->actingAs($this->makeAdmin());

    Livewire::test(ListFaqs::class)
        ->callTableBulkAction('delete', $faqs)
        ->assertOk();

    foreach ($faqs as $faq) {
        $this->assertSoftDeleted($faq);
    }
}
```

### 6. Validation — assert specific rule failures

```php
public function test_question_is_required(): void
{
    $this->actingAs($this->makeAdmin());
    Livewire::test(CreateFaq::class)
        ->fillForm(['question' => ['pl' => '', 'en' => '']])
        ->call('create')
        ->assertHasFormErrors(['question' => 'required']);
}

public function test_slug_must_be_unique(): void
{
    Faq::factory()->create(['slug' => 'taken']);
    $this->actingAs($this->makeAdmin());
    Livewire::test(CreateFaq::class)
        ->fillForm(['slug' => 'taken'])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
}
```

### 7. Migration rollback — test `down()` actually works

```php
public function test_migration_is_reversible(): void
{
    $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

    $this->assertTrue(Schema::hasTable('faqs'));

    $this->artisan('migrate:rollback')->assertSuccessful();

    $this->assertFalse(Schema::hasTable('faqs'));
}
```

### 8. Queue & events — use fakes explicitly

```php
public function test_webhook_dispatches_notification_job(): void
{
    Queue::fake();

    $this->postJson(route('webfloo.leads.webhook'), [
        'email' => 'test@example.com',
        'name' => 'Test',
    ])->assertCreated();

    Queue::assertPushed(NotifyLeadOwnerJob::class);
}
```

### 9. Factories — use them everywhere for arrange

```php
Faq::factory()->active()->count(5)->create();
Faq::factory()->featured()->create(['is_active' => false]);
```

If factory missing — add one in `database/factories/`. Tests without factories = flaky + slow.

### 10. Named test methods describe behavior

```php
// GOOD
public function test_inactive_faqs_are_excluded_from_public_scope(): void

// BAD
public function test_faq(): void
public function test_scope(): void
public function test_it_works(): void
```

## DON'T ✗

### 1. ✗ Mocking the database

```php
// BAD
$mock = Mockery::mock(Faq::class);
$mock->shouldReceive('where')->andReturn(...);
```

**Why:** mocks test your mock, not your code. Real SQLite `:memory:` is <10ms — no excuse.

### 2. ✗ Tautology asserts

```php
// BAD
$this->assertTrue(true);
$this->assertNotNull($faq);
$this->assertIsString($faq->question);

// GOOD
$this->assertSame('Pytanie?', $faq->getTranslation('question', 'pl'));
$this->assertCount(3, $response->json('data'));
$this->assertDatabaseHas('faqs', ['slug' => 'taken']);
```

`assertNotNull` is fine as part of a pipeline but not as the only assertion.

### 3. ✗ Testing only happy path

Every public method needs: happy path + 1 edge + 1 failure path. If not → not tested.

### 4. ✗ Skipping authorization tests

Every Filament Resource needs tests for:
- unauthorized user is blocked on index/create/edit/delete
- user with right permission passes

Missing = production security hole.

### 5. ✗ Testing translatable fields with only one locale

`['question' => 'foo']` writes to current app locale only. The JSON structure underneath can be broken and you'd never know.

### 6. ✗ `WithoutMiddleware` / `WithoutEvents` globally

Bypasses what you're testing. Only disable specific middleware/events with explicit rationale.

### 7. ✗ `@skip` / `markTestSkipped` without GitHub issue link

If skipping, link the issue that will unskip it. Otherwise it's dead code.

### 8. ✗ Live HTTP / external service calls

Always fake: `Http::fake()`, `Storage::fake()`, `Queue::fake()`, `Mail::fake()`, `Event::fake()`.

### 9. ✗ Shared state between tests

Each test = fresh DB (via `RefreshDatabase`), no static caches, no singletons that survive. If tests pass in order but fail when reordered — bug.

### 10. ✗ Time-dependent tests without `Carbon::setTestNow()`

```php
// BAD
$this->assertSame(now()->toDateString(), $faq->published_at->toDateString());

// GOOD
Carbon::setTestNow('2026-04-17 12:00:00');
$faq = Faq::factory()->create();
$this->assertSame('2026-04-17', $faq->published_at->toDateString());
```

## Filament Resource test checklist (apply to every Resource)

For each `XxxResource`, ensure tests exist for:

- [ ] List page renders for authorized user
- [ ] Index denies unauthorized user (`assertForbidden`)
- [ ] Create form submits valid data → record persists
- [ ] Create form rejects invalid data (validation asserted)
- [ ] Edit form prefills existing record (check Livewire state)
- [ ] Edit saves changes
- [ ] Delete action (or soft delete) works + is authorized
- [ ] Bulk delete works + is authorized
- [ ] Table columns show expected data (use `assertCanSeeTableRecords`)
- [ ] Table filters work (if Resource has filters)
- [ ] Translatable fields: both locales save/load correctly (if applicable)
- [ ] Policy: every relevant `can*` method tested (viewAny, create, update, delete)
- [ ] Scopes used by Resource (e.g. `scopeActive`) have their own unit tests

A Resource hitting <7/10 of these = SCORE < 7, skeleton proposal required.

## Scoring rubric (audit pass)

| Score | Meaning |
|---|---|
| 10 | All checklist items + edge cases + meaningful asserts |
| 7-9 | Core checklist covered, minor gaps (filters, bulk actions) |
| 4-6 | Happy path only, or mocks DB, or missing policy tests |
| 1-3 | Tautology asserts, WithoutMiddleware, mocks everywhere |
| 0 | No test file exists |

Score < 7 → propose test skeleton.

## Cross-platform execution

Always run from repo root:

```bash
cd /Users/michal/DEV/webfloo
./vendor/bin/phpunit                         # all tests
./vendor/bin/phpunit --filter=FaqResourceTest # single test class
./vendor/bin/phpunit --filter=FaqResourceTest::test_unauthorized_user_cannot_access
./vendor/bin/phpunit --testdox               # human-readable output
```

**Never** `php artisan test` — this is a Composer package, not a Laravel app. There is no `artisan` binary here.

Works identically on Linux and macOS. On Windows use `vendor\bin\phpunit`.

## CI integration

`.github/workflows/check.yml` runs on every push/PR to main. Local pre-commit — run full suite: `./vendor/bin/phpunit && ./vendor/bin/phpstan && ./vendor/bin/pint --test`.

## Appendix: common helpers to add to `tests/TestCase.php`

```php
protected function makeAdmin(array $permissions = ['*']): User
{
    $user = User::factory()->create();
    foreach ($permissions as $perm) {
        $user->givePermissionTo($perm);
    }
    return $user;
}

protected function defineDatabaseMigrations(): void
{
    $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
}
```

These plus `use RefreshDatabase` in each test class = zero-boilerplate test writing.

---

# Code Quality Rules (MANDATORY — agents read this)

These rules apply to every test (and every helper/factory/scaffold) generated in this package. Violations block merge. Zero exceptions.

## 1. No hardcoded paths in test logic

**Bad:**
```php
$file = '/Users/michal/DEV/webfloo/tests/fixtures/lead.json';
$this->artisan('migrate --path=/home/runner/work/webfloo/database/migrations');
```

**Good:**
```php
$file = __DIR__ . '/../fixtures/lead.json';
$this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
```

**Why:** hardcoded absolute paths break CI (different box), break other devs (different homedir), break Docker. Use `__DIR__` relative or Laravel helpers (`base_path()`, `database_path()`, `storage_path()`) inside application code. In tests, prefer `__DIR__` relatives to stay explicit about position.

**Allowed exceptions:** documentation examples, commit messages, state files (`.testing-audit-state.md`) — never code.

## 2. SSOT — Single Source of Truth

Every fact/rule/constant exists in exactly ONE place. Tests reference it — they don't duplicate it.

**Bad:**
```php
// test_A.php
$this->assertSame(25, Lead::where('status', 'new')->limit(25)->count());

// test_B.php (duplicated magic number)
$leads = Lead::factory()->count(25)->create();
```

**Good:**
```php
// config/bitfloo.php
'crm' => ['kanban_column_limit' => 25],

// test_A.php
$limit = config('bitfloo.crm.kanban_column_limit');
$this->assertSame($limit, Lead::where('status', 'new')->limit($limit)->count());
```

**Rule:** if you see the same literal value twice in tests, promote it to config OR constant OR test helper.

## 3. KISS — Keep It Simple, Stupid

**No premature abstraction in tests.** Tests are read 10× more than written — clarity beats cleverness.

**Bad (over-engineered):**
```php
protected function createLeadWithFullRelations(array $overrides = [], int $tagCount = 2, int $reminderCount = 1, bool $withOwner = true, bool $withEmail = true): Lead {
    // 40 lines of branching logic
}
```

**Good (inline what the test actually needs):**
```php
public function test_tags_sync_on_edit(): void
{
    $lead = Lead::factory()->create();
    $tag = LeadTag::factory()->create();
    // ...
}
```

**Rules:**
- Max 3 helper methods per test class. More = move to `tests/TestCase.php` (if generic) or split the test class.
- No `BaseLeadTest extends TestCase` hierarchy unless 3+ test classes share real logic (not just factory calls).
- Factory traits (`->active()`, `->featured()`) are the right abstraction for test data. Use them instead of wrapper methods.

## 4. Modular test structure

**One test class = one Resource/Model/behavior.** Long classes split by concern:

```
tests/Feature/Filament/
  LeadResourceTest.php           ← CRUD + table + form basics
  LeadResourceActionsTest.php    ← custom Actions (send_email, export)
  LeadResourcePermissionsTest.php ← policy coverage (complex roles)
  LeadResourceTagsTest.php       ← relation management
```

**Rule:** if a test file exceeds ~400 LOC, split by concern. Never just "more tests in the same file".

## 5. Comment policy — only WHY, never WHAT

**Default: write NO comments.**

Add a comment ONLY when:
- Explaining WHY a non-obvious choice was made
- Documenting a subtle invariant the code can't express
- Linking to an issue/decision (`// see ADR-008`)
- Marking a workaround (`// workaround for Filament v5 bug #XXXXX`)

**Never:**
```php
// Create a Lead with email
$lead = Lead::factory()->create(['email' => 'x@y.pl']);

// Assert the lead has the email
$this->assertSame('x@y.pl', $lead->email);

// Test that translatable field works
public function test_translatable_field_works(): void { }
```

Self-documenting code + precise test method names = NO narration. If a reviewer asks "what does this line do?" fix the NAMING, don't add a comment.

## 6. Precise test names

Name = specification:

| Bad | Good |
|---|---|
| `test_faq()` | `test_create_faq_persists_with_both_locales()` |
| `test_scope()` | `test_scope_published_excludes_future_dates()` |
| `test_it_works()` | `test_unauthorized_user_cannot_view_leads_index()` |
| `test_delete()` | `test_bulk_delete_requires_delete_any_permission()` |

Format: `test_<subject>_<expected_behavior>_<under_condition>`.

## 7. No dead code

- No commented-out test methods (use `markTestSkipped` with issue link, or delete)
- No unused imports
- No unused protected properties
- No `public function test_foo(): void { $this->markTestIncomplete(); }` without issue reference

## 8. Deterministic tests

- No `rand()` without `Faker`'s `$this->faker->randomNumber()` (seeded)
- No `sleep()`
- No `now()` without `Carbon::setTestNow()` when outcome depends on time
- No network calls (always `Http::fake()`)
- No real filesystem beyond `Storage::fake()` + `UploadedFile::fake()`

## 9. Agent-consumption summary

When an agent (`filament-resource-tester`, `livewire-interaction-tester`, `translatable-field-guardian`, `policy-coverage-auditor`) generates code, it MUST:

1. Use relative paths (`__DIR__`, `base_path()`, `database_path()`).
2. Reference config/constants for magic values — not inline numbers.
3. Prefer factory states over custom helper methods.
4. Skip comments unless meeting criteria in §5.
5. Use precise test names per §6.
6. Split into modular files when exceeding ~400 LOC per §4.
7. Use deterministic fakes per §8.

If these rules conflict with a specific pattern above in this document, the pattern wins for THAT specific case (e.g. translatable field tests MAY contain a comment explaining locale fallback semantics — that's "WHY"). But default is terse.
