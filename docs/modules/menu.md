# Module: Menu

**Feature flag:** `webfloo.features.menu` (default `true`)
**Scope:** konfigurowalna nawigacja z lokalizacjami (header, footer, sidebar, mobile), hierarchia parent/child, sort order, target (_self / _blank).

## Public API

### Resources
- `Webfloo\Filament\Resources\MenuItemResource`

### Models
- `Webfloo\Models\MenuItem` — label, href, target, location, parent_id, is_active, sort_order

### Traits applied
- `HasActive`, `Sortable`, `HasTranslations` (label)

### Relationships
- `MenuItem::belongsTo(MenuItem, 'parent_id')` — parent (nullable)
- `MenuItem::hasMany(MenuItem, 'parent_id')` — children

### Static helpers
- `MenuItem::getAllGroupedByLocation(): array<string, Collection<int, MenuItem>>` — **CACHED** (24h TTL) — zwraca wszystkie aktywne items z eager-loaded children, grouped by location. Cache invalidated na `saved` / `deleted` hook.

## Migrations

- `*_create_menu_items_table` (label, href, target, location enum, parent_id FK nullable, is_active, sort_order)

## Shield permissions

```
view_any_menu_item   view_menu_item   create_menu_item   update_menu_item   delete_menu_item
```

Standard role assignments (super_admin all, editor all CRUD, viewer view only).

## Host integration

### Render w frontend

Pakiet **nie dostarcza** menu rendering component. Host renderuje:

```blade
@php
    $menus = \Webfloo\Models\MenuItem::getAllGroupedByLocation();
    $header = $menus['header'] ?? collect();
@endphp

<nav>
    @foreach($header as $item)
        <a href="{{ $item->href }}" target="{{ $item->target }}">
            {{ $item->label }}
        </a>
        @if($item->children->isNotEmpty())
            <ul class="dropdown">
                @foreach($item->children as $child)
                    <li><a href="{{ $child->href }}">{{ $child->label }}</a></li>
                @endforeach
            </ul>
        @endif
    @endforeach
</nav>
```

Alternatywnie przez Inertia props w `HandleInertiaRequests::share()`.

### Location enum

Default lokalizacje (hardcoded w `MenuItem::getLocationOptions()`):
- `header` — główna nawigacja
- `footer` — stopka
- `sidebar` — menu boczne (opcjonalne)
- `mobile` — mobile-only nav

Host może dodać własne przez subclass albo override accessor.

### Feature flag scenarios

- `features.menu = false`:
  - MenuItemResource niewidoczny.
  - `MenuItem::getAllGroupedByLocation()` nadal działa programowo (returns empty lub stale cache).
  - Host frontend rendering dostaje empty collection.

## Testing

1. Navigate `/admin/menu-items` → listing.
2. Create parent MenuItem + 2 children → check hierarchical display.
3. Toggle `is_active` → `MenuItem::getAllGroupedByLocation()` wyklucza inactive.
4. Reorder (drag-drop) → `sort_order` updates + cache invalidated → next render uses new order.

## Performance

- `getAllGroupedByLocation()` wywoływane per-request przez host's frontend layer. **Cached** via `Cache::remember('webfloo.menu_items.grouped_by_location', 86400, ...)`.
- Invalidation automatic na `MenuItem::saved` / `deleted` (patrz `booted()` hook w modelu).
- 24h TTL fallback dla edge case gdzie event nie odpalił (np. direct SQL manipulation).

## Limitations / known gaps

- **Zero built-in i18n dla locations** — "header", "footer" hardcoded stringi (nie `__()`).
- **Brak permissions per location** — user z `update_menu_item` może edytować każdą location. Follow-up: location-scoped permissions dla multi-tenant scenariuszy.
- **Brak menu templates / presets** — host wire'uje własne render logic. Follow-up: `<x-webfloo-menu location="header">` Blade component.
