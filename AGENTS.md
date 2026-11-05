# locations: notes for AI coding agents

`javaabu/locations` is a Laravel package for countries, states, cities, atolls and islands. This file grows with the package; see `docs/` for usage.

## Rules

- Follow the Javaabu PHP guidelines (`vendor/javaabu/boost-javaabu-guidelines`): snake_case variables and properties, camelCase methods, `#[Test]` methods named `it_…`, early returns, no `else`.
- Never add `javaabu/helpers` (or anything that pulls in `javaabu/settings ^1`) as a production dependency. CI checks.

## Commands

```bash
composer install
composer lint && composer test        # Pint and PHPUnit
```
