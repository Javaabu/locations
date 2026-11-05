# locations: notes for AI coding agents

`javaabu/locations` is a Laravel package for countries, states, cities, atolls and islands. It ships seed data (JSON in `database/data`, built from cited sources), models, cascading location selects (Blade components, React components in `resources/js`, stencil fields), and admin pages in Blade (javaabu/forms v2) and Inertia React (@javaabu/forms), delivered to apps as `@javaabu/locations` through javaabu/manifest.

## Layout

| Path | What |
| --- | --- |
| `src/Locations.php` | Model, table and resource resolution: always go through it, never name a model class directly |
| `src/Models/` | The five models, `Concerns/IsLocation` (shared), and `Translatable/` variants for javaabu/translatable |
| `src/Data`, `src/Seeding`, `src/Commands`, `database/seeders` | Reading the JSON data, `LocationsImporter`, `locations:seed`, the seeders |
| `src/Routing`, `src/Http` | `Route::locations()` / `Route::locationOptions()`, the controllers (one per type on `LocationsController`), `LocationResource` |
| `src/Forms`, `src/Stencil` | Stencil forms of the admin pages, and the stencil location select fields |
| `src/Views` | `ViewRenderer` (Blade or Inertia) and the Blade select components |
| `src/Support` | `LocationOptions` (the filters shared by selects, the endpoint and the index pages), `Translations` (the only place that touches javaabu/translatable) |
| `resources/views`, `resources/js` | Blade pages and components; React selects, pages (`Pages/Resources/*`) and `types.ts` |
| `database/data`, `bin/` | Seed data and its sources; `update-sources.php`, `build-data.php`, `validate-data.php` |
| `tests/` | PHPUnit (`Unit`, `Feature`, `Http`, `Translatable`) and Vitest (`tests/js`, with fixtures written by `tests/Http/InertiaPagesTest.php`) |
| `docs`, `llms.txt`, `resources/boost` | Docs, the index for assistants, the Laravel Boost guideline and skill |

## Seed data

- `database/data/*.json` is what the seeders read. `countries.json`, `states.json`, `islands.json` and `REVIEW.md` are **generated** by `php bin/build-data.php` from the snapshots in `database/data/sources/`; never edit them by hand. `atolls.json` is curated by hand.
- Fix a Dhivehi country name in `sources/javaabu-country-names.dv.json`, an English display name in `sources/javaabu-country-names.en.json`, then rebuild. Refresh the snapshots with `php bin/update-sources.php` (pinned versions at the top).
- Run `php bin/validate-data.php` after any change. CI rebuilds the data and fails if it differs from what's committed.
- Never guess a name, code or translation: flag it in `REVIEW.md` (through the build script) and in `docs/advanced-usage/data-sources.md`.

## Rules

- Follow the Javaabu PHP guidelines (`vendor/javaabu/boost-javaabu-guidelines`): snake_case variables and properties, camelCase methods, `#[Test]` methods named `it_…`, early returns, no `else`.
- Never add `javaabu/helpers` (or anything that pulls in `javaabu/settings ^1`) as a production dependency. CI checks.
- javaabu/translatable and inertiajs/inertia-laravel are optional: only `Support/Translations.php`, `Models/Translatable/` and `Views/ViewRenderer.php` may reference them, behind `class_exists`. CI runs the suite without them; their tests skip themselves.
- The Dhivehi name column is `dhivehi_name`, never `name_dv` (translatable's suffix naming).
- A change to a page's props changes `resources/js/types.ts`, the pages and `docs/basic-usage/admin-pages.md` together. Then run `LOCATIONS_UPDATE_FIXTURES=1 vendor/bin/phpunit --filter InertiaPagesTest` and commit `tests/js/fixtures`.
- Every user-facing string goes in `lang/en/strings.php` and `lang/dv/strings.php` (same keys and placeholders; `LocalizationTest` checks), permission descriptions in `lang/dv.json`. Views and components use logical directions only. List uncertain Dhivehi in `docs/advanced-usage/dhivehi-and-rtl.md`.
- Every change needs tests, docs (linked from `llms.txt`; `DocsTest` checks links and config coverage), the Boost skill if it changes usage, and a line in `CHANGELOG.md`.

## Commands

```bash
composer install && pnpm install      # pnpm 10.28 (packageManager)
composer lint && composer test        # Pint and PHPUnit
pnpm lint && pnpm format:check && pnpm typecheck && pnpm test
php bin/build-data.php && php bin/validate-data.php
```
