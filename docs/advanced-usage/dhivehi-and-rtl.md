---
title: Dhivehi and RTL
sidebar_position: 8
---

# Strings

Every string the pages, selects and messages show is in `lang/en/strings.php` and `lang/dv/strings.php` (`__('locations::strings.…')`). The permission descriptions and group labels are in `lang/dv.json`. Publish them to change any line:

```bash
php artisan vendor:publish --tag=locations-translations
```

# Names

Locations show their `dhivehi_name` for the locales in `config('locations.dhivehi_locales')` (`['dv']`), and their English `name` otherwise. This covers page titles, table cells, select options and the options endpoint. Atolls show their Thaana code (`ހއ. ހާ އަލިފު`), and islands their atoll's (`ހއ. ދިއްދޫ`).

Atolls are ordered north to south in every language. Everything else is ordered by its name in the current language. In Dhivehi, that's the Thaana alphabet order (ހ ށ ނ ރ ބ …), with locations that have no Dhivehi name last.

# Right to left

- The views and components use logical properties only (`ms-`, `pe-`, `start-`, `text-start`); a test checks.
- Dhivehi names and official Dhivehi names are shown with `dir="rtl"`, codes and numbers with `dir="ltr"`, and English names with `dir="auto"`.
- The Dhivehi name fields of the admin forms are Thaana inputs (stencil's `thaana()`): users type Thaana from a Latin keyboard. With javaabu/translatable, each Dhivehi name input is one.
- Search matches Thaana as well as Latin text.

# For review

A Dhivehi speaker should check these:

**Strings** (`lang/dv/strings.php`, `lang/dv.json`):

- The island categories: ރައްޔިތުން ދިރިއުޅޭ ރަށް, ފަތުރުވެރިކަމުގެ ރަށް, ސިނާޢީ ރަށް, މުއައްސަސާތަކުގެ ރަށް (institutional), މީހުން ނުދިރިއުޅޭ ރަށް.
- Field names that are transliterations: ލެޓިޓިއުޑް, ލޮންގިޓިއުޑް, ކޯލިންގ ކޯޑު, އަލްފާ-3 ކޯޑު, ސްޓޭޓް.
- `is_city` (ސިޓީ ކައުންސިލަކުން ހިންގާ) and `sections.geography` (ޖުޣްރާފީ މަޢުލޫމާތު).
- The messages built from a type: `:type އުފެއްދިއްޖެ`, `އަދި އެއްވެސް :type ނެތް` (with the plural, ރަށްތައް), and `:children ހުރިހާ ހިނދަކު މި :type ނުފޮހެލޭނެ`.

**Data** (`database/data/REVIEW.md`, see [Data sources](./data-sources.md#for-review)):

- The 53 Dhivehi country names translated for this package and the 5 iso-codes names replaced.
- The atolls' Dhivehi names (the letter names, ހާ އަލިފު) and official Dhivehi names.
