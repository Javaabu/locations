---
title: Data sources
sidebar_position: 1
---

The seed data is plain JSON in `database/data`, so every change shows up in a diff. `bin/build-data.php` builds it from snapshots of authoritative sources in `database/data/sources`. Only the atolls are curated by hand.

| File | What | Built from |
| --- | --- | --- |
| `countries.json` | All 249 ISO 3166-1 countries | ISO 3166-1, Dhivehi names, calling codes (below) |
| `atolls.json` | 20 administrative atolls and Malé City | Curated by hand |
| `islands.json` | All 1,560 features of the national island register (1,131 named) | The MLSA island register |
| `states.json` | 3,569 top-level ISO 3166-2 subdivisions, every country except the Maldives | ISO 3166-2 |
| `REVIEW.md` | What couldn't be verified and needs a human | Generated |

# Sources

## Countries

| Field | Source |
| --- | --- |
| `code`, `alpha3_code`, `numeric_code`, `official_name` | **ISO 3166-1**, through Debian's [iso-codes](https://salsa.debian.org/iso-codes-team/iso-codes) v4.20.1 (`data/iso_3166-1.json`), which tracks the [ISO Online Browsing Platform](https://www.iso.org/obp/ui/#search/code/) |
| `name` | iso-codes' `common_name` when it has one (`Bolivia` for `Bolivia, Plurinational State of`). Otherwise the ISO short name, with inverted names written out (`Democratic Republic of the Congo`). The ISO short name stays in the data file as `iso_name` |
| `dhivehi_name` | iso-codes' community Dhivehi translations (`iso_3166-1/dv.po`, unconfirmed "fuzzy" entries left out) for 191 countries. The other 53 were translated for this package, and 5 iso-codes names were replaced. `dhivehi_name_source` in the data file says which is which; both are listed for review |
| `calling_code` | Google's [libphonenumber](https://github.com/google/libphonenumber) v9.0.42 metadata (`PhoneNumberMetadata.xml`) |

## Atolls

| Field | Source |
| --- | --- |
| `code`, `letter_code`, `dhivehi_code` | The Maldivian government's administrative atoll codes: Latin abbreviation (`HA`), letter code (`A`), Thaana abbreviation (`ހއ`) |
| `iso_code` | [ISO 3166-2:MV](https://www.iso.org/obp/ui/#iso:code:3166:MV), as last changed on 2018-11-26 (`MV-07`, …, `MV-MLE`) |
| `name` | The atoll names of the island register (`Haa Alifu`, `Alifu Dhaalu`) |
| `dhivehi_name` | The Thaana letter names of the code (`ހާ އަލިފު`) |
| `official_name`, `official_dhivehi_name` | The administrative names (`Thiladhunmathi Uthuruburi` / `ތިލަދުންމަތީ އުތުރުބުރި`), as the [Local Government Authority](https://www.lga.gov.mv/en/councils) names its councils |
| `is_city` | Whether one city council governs the whole atoll: Malé, Addu and Fuvahmulah |

## Islands

| Field | Source |
| --- | --- |
| `code` | The island register's feature code (`FCODE`, e.g. `LD0442`) |
| `name`, `dhivehi_name` | The register's English and Thaana names, spelled as it spells them |
| `category` | The register's category: residential, tourism, industrial, institutional or uninhabited |
| `is_capital` | The register's atoll capital flag |
| `latitude`, `longitude` | The register's coordinates, converted from degrees, minutes and seconds to decimal degrees (WGS 84) |
| `area` | The register's land area, in hectares |

The register is the **Official Island List** of [OneMap](https://readme.onemap.mv/), published by the Geomatics Department of the Ministry of Infrastructure, Housing and Urban Development (formerly the Maldives Land and Survey Authority): [`IslandList_20211101.csv`](https://readme.onemap.mv/csv/IslandList_20211101.csv). On 2026-11-05 it was identical to OneMap's live `island_20240509` layer (last edited 2026-10-21).

Inhabited islands are cross-checked against the councils of the [Local Government Authority](https://www.lga.gov.mv/en/councils) (`sources/lga-councils.json`). Every island council matches a residential island of the register. Seven of them only differ in spelling (`Kurinbee` and the register's `Kurin'bi`), and they're listed in `REVIEW.md`. The register's names are kept.

## States

| Field | Source |
| --- | --- |
| `code`, `name`, `type` | **ISO 3166-2**, through iso-codes v4.20.1 (`data/iso_3166-2.json`): the subdivisions without a parent |

# Licences and attribution

The package's code is MIT. The data keeps its sources' terms:

- **Island register:** released under the [Data Usage License](https://readme.onemap.mv/pdf/Data%20Usage%20License.pdf) of the Geomatics Department, which allows copying, adapting and redistributing it, commercially too, with attribution. Apps that show the data should credit it: *Contains information licensed under Data Usage License – Geomatics Department of Ministry of Infrastructure, Housing and Urban Development.*
- **iso-codes** (country and subdivision names, Dhivehi country names): LGPL-2.1-or-later. The codes themselves are ISO's.
- **libphonenumber** (calling codes): Apache-2.0.

:::note For the maintainers
The LGPL covers iso-codes' translations. Shipping them as data next to MIT code is common practice, but confirm it suits Javaabu before the first release.
:::

# For review

`database/data/REVIEW.md` lists what couldn't be verified against an authoritative source:

- the 53 Dhivehi country names translated for this package, and the 5 iso-codes names replaced;
- the Dhivehi names and official Dhivehi names of the atolls;
- one island whose category in the register is a typo (`LD0021` Ariyadhoo, seeded as uninhabited);
- the seven council spellings that differ from the register;
- the countries libphonenumber has no calling code for.

# Updating the data

```bash
php bin/update-sources.php   # download the pinned source versions into database/data/sources
php bin/build-data.php       # rebuild countries.json, states.json, islands.json and REVIEW.md
php bin/validate-data.php    # codes, uniqueness, references, Thaana names, coordinates
git diff database/data       # review every change
```

To update a source, bump its version at the top of `bin/update-sources.php`. To fix a Dhivehi country name, edit `sources/javaabu-country-names.dv.json`. To fix an atoll, edit `atolls.json`. Then rebuild. CI rebuilds the data and fails if the committed files don't match their sources.

Apps get new data with `php artisan locations:seed --update` (see [Seeding](../basic-usage/seeding.md)).
