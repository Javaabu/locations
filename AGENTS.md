# locations: notes for AI coding agents

`javaabu/locations` is a Laravel package for countries, states, cities, atolls and islands. This file grows with the package; see `docs/` for usage.

## Seed data

- `database/data/*.json` is what the seeders read. `countries.json`, `states.json`, `islands.json` and `REVIEW.md` are **generated** by `php bin/build-data.php` from the snapshots in `database/data/sources/`; never edit them by hand. `atolls.json` is curated by hand.
- Fix a Dhivehi country name in `sources/javaabu-country-names.dv.json`, an English display name in `sources/javaabu-country-names.en.json`, then rebuild. Refresh the snapshots with `php bin/update-sources.php` (pinned versions at the top).
- Run `php bin/validate-data.php` after any change. CI rebuilds the data and fails if it differs from what's committed.
- Never guess a name, code or translation: flag it in `REVIEW.md` (through the build script) and in `docs/advanced-usage/data-sources.md`.

## Rules

- Follow the Javaabu PHP guidelines (`vendor/javaabu/boost-javaabu-guidelines`): snake_case variables and properties, camelCase methods, `#[Test]` methods named `it_…`, early returns, no `else`.
- Never add `javaabu/helpers` (or anything that pulls in `javaabu/settings ^1`) as a production dependency. CI checks.

## Commands

```bash
composer install
composer lint && composer test        # Pint and PHPUnit
```
