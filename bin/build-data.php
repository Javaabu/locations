<?php

/**
 * Builds the seed data in database/data from the source snapshots in database/data/sources.
 *
 *     php bin/build-data.php
 *
 * Writes countries.json, states.json, islands.json and REVIEW.md. atolls.json is curated by
 * hand and only read. Review the diff before committing.
 */
$data = __DIR__ . '/../database/data';
$sources = "{$data}/sources";

function readJson(string $path): array
{
    return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
}

function writeJson(string $path, array $contents): void
{
    file_put_contents($path, json_encode($contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
    echo 'Wrote ' . basename($path) . "\n";
}

/**
 * Translations in a gettext file, leaving out fuzzy (unconfirmed) entries
 *
 * @return array<string, string> msgid => msgstr
 */
function readPo(string $path): array
{
    preg_match_all('/((?:#[^\n]*\n)*)msgid "((?:[^"\\\\]|\\\\.)*)"\nmsgstr "((?:[^"\\\\]|\\\\.)*)"/', file_get_contents($path), $matches, PREG_SET_ORDER);

    $translations = [];

    foreach ($matches as [, $comments, $id, $translation]) {
        if (str_contains($comments, 'fuzzy') || $translation === '') {
            continue;
        }

        $translations[stripcslashes($id)] = stripcslashes($translation);
    }

    return $translations;
}

/**
 * A coordinate in degrees, minutes and seconds (`73° 10' 17.008" E`) as decimal degrees
 */
function dmsToDecimal(string $value): ?float
{
    if (! preg_match('/^\s*(\d+)°\s*(\d+)\'\s*([\d.]+)"\s*([NSEW])\s*$/u', $value, $match)) {
        return null;
    }

    $decimal = (int) $match[1] + ((int) $match[2] / 60) + ((float) $match[3] / 3600);

    if (in_array($match[4], ['S', 'W'], true)) {
        $decimal = -$decimal;
    }

    return round($decimal, 6);
}

function clean(?string $value): ?string
{
    $value = trim(preg_replace('/\s+/u', ' ', (string) $value));

    return $value === '' ? null : $value;
}

$review = [];

/*
|--------------------------------------------------------------------------
| Countries
|--------------------------------------------------------------------------
*/

$iso_countries = readJson("{$sources}/iso_3166-1.json")['3166-1'];
$iso_dhivehi = readPo("{$sources}/iso_3166-1.dv.po");
$javaabu_dhivehi = readJson("{$sources}/javaabu-country-names.dv.json")['names'];
$english_names = readJson("{$sources}/javaabu-country-names.en.json")['names'];
$calling_codes = readJson("{$sources}/libphonenumber-calling-codes.json")['calling_codes'];

$countries = [];

foreach ($iso_countries as $country) {
    $code = $country['alpha_2'];
    $name = $english_names[$code] ?? $country['common_name'] ?? $country['name'];

    $dhivehi_name = $javaabu_dhivehi[$code]['dhivehi_name']
        ?? (isset($country['common_name']) ? ($iso_dhivehi[$country['common_name']] ?? null) : null)
        ?? $iso_dhivehi[$country['name']]
        ?? null;

    $countries[] = [
        'code' => $code,
        'alpha3_code' => $country['alpha_3'],
        'numeric_code' => $country['numeric'],
        'name' => $name,
        'iso_name' => $country['name'],
        'official_name' => $country['official_name'] ?? null,
        'dhivehi_name' => $dhivehi_name,
        'dhivehi_name_source' => isset($javaabu_dhivehi[$code]) ? 'javaabu' : ($dhivehi_name ? 'iso-codes' : null),
        'calling_code' => $calling_codes[$code] ?? null,
    ];

    if (isset($javaabu_dhivehi[$code])) {
        $review['countries'][] = "| {$code} | {$name} | {$dhivehi_name} | {$javaabu_dhivehi[$code]['note']} |";
    }

    if (! isset($calling_codes[$code])) {
        $review['calling_codes'][] = "- {$code} {$name}";
    }
}

usort($countries, fn (array $a, array $b) => strcmp($a['code'], $b['code']));

writeJson("{$data}/countries.json", [
    'description' => 'Every ISO 3166-1 country, with English and Dhivehi names and calling codes. Built by bin/build-data.php; see docs/advanced-usage/data-sources.md.',
    'sources' => [
        'code, alpha3_code, numeric_code, iso_name, official_name' => 'ISO 3166-1, through Debian iso-codes (https://salsa.debian.org/iso-codes-team/iso-codes) data/iso_3166-1.json, LGPL-2.1-or-later',
        'name' => 'iso-codes common_name when it has one, else the ISO short name; inverted names are written out (sources/javaabu-country-names.en.json)',
        'dhivehi_name' => 'iso-codes iso_3166-1/dv.po (dhivehi_name_source: iso-codes), or translated for this package (dhivehi_name_source: javaabu, for review)',
        'calling_code' => 'Google libphonenumber metadata (https://github.com/google/libphonenumber), Apache-2.0',
    ],
    'countries' => $countries,
]);

/*
|--------------------------------------------------------------------------
| States: top-level ISO 3166-2 subdivisions
|--------------------------------------------------------------------------
*/

$states = [];

foreach (readJson("{$sources}/iso_3166-2.json")['3166-2'] as $subdivision) {
    if (isset($subdivision['parent'])) {
        continue;
    }

    $country = substr($subdivision['code'], 0, 2);

    if ($country === 'MV') {
        continue;
    }

    $states[] = [
        'code' => $subdivision['code'],
        'country' => $country,
        'name' => $subdivision['name'],
        'type' => $subdivision['type'],
    ];
}

usort($states, fn (array $a, array $b) => strcmp($a['code'], $b['code']));

writeJson("{$data}/states.json", [
    'description' => 'The top-level ISO 3166-2 subdivisions of every country except the Maldives (whose subdivisions are the atolls). Built by bin/build-data.php.',
    'sources' => [
        'code, name, type' => 'ISO 3166-2, through Debian iso-codes data/iso_3166-2.json, LGPL-2.1-or-later',
    ],
    'states' => $states,
]);

/*
|--------------------------------------------------------------------------
| Islands
|--------------------------------------------------------------------------
*/

$atolls = [];

foreach (readJson("{$data}/atolls.json")['atolls'] as $atoll) {
    $atolls[$atoll['register_name']] = $atoll;
}

$categories = [
    'Residential Island' => 'residential',
    'Tourism Island' => 'tourism',
    'Industrial Island' => 'industrial',
    'Institutional Island' => 'institutional',
    'Uninhabited Island' => 'uninhabited',
];

$handle = fopen("{$sources}/onemap-IslandList_20211101.csv", 'r');
$header = array_map(fn (string $column) => trim($column, "\u{FEFF} "), fgetcsv($handle, escape: ''));
$islands = [];

while (($row = fgetcsv($handle, escape: '')) !== false) {
    $row = array_combine($header, $row);
    $code = $row['FCODE'];
    $atoll = $atolls[trim($row['atoll'])] ?? null;

    if (! $atoll) {
        fwrite(STDERR, "Unknown atoll [{$row['atoll']}] for {$code}\n");
        exit(1);
    }

    $register_category = clean($row['category']);
    $category = $categories[$register_category] ?? null;

    if (! $category) {
        $category = 'uninhabited';
        $review['islands'][] = "| {$code} | {$atoll['code']}. " . clean($row['islandName']) . " | The register's category is `{$register_category}` (sector: " . clean($row['Sector']) . '); seeded as uninhabited |';
    }

    $islands[] = [
        'code' => $code,
        'atoll' => $atoll['code'],
        'name' => clean($row['islandName']),
        'dhivehi_name' => clean($row['islandNa_1']),
        'category' => $category,
        'is_capital' => trim($row['capital']) === 'Y',
        'latitude' => dmsToDecimal($row['latitude']),
        'longitude' => dmsToDecimal($row['longitude']),
        'area' => is_numeric($row['Area_ha']) ? round((float) $row['Area_ha'], 4) : null,
        'sector' => clean($row['Sector']),
        'usage' => clean($row['Usage']),
    ];
}

fclose($handle);

usort($islands, fn (array $a, array $b) => strcmp($a['code'], $b['code']));

writeJson("{$data}/islands.json", [
    'description' => 'Every island of the Maldives in the national island register, keyed by its feature code. Built by bin/build-data.php; see docs/advanced-usage/data-sources.md.',
    'attribution' => 'Contains information licensed under Data Usage License – Geomatics Department of Ministry of Infrastructure, Housing and Urban Development (https://readme.onemap.mv/pdf/Data%20Usage%20License.pdf).',
    'sources' => [
        'code, name, dhivehi_name, category, is_capital, latitude, longitude, area, sector, usage' => 'OneMap Official Island List (https://readme.onemap.mv/csv/IslandList_20211101.csv), Maldives Land and Survey Authority / Geomatics Department. Identical to the live island_20240509 layer as of 2026-11-05',
        'atoll' => 'The register\'s atoll, as its code in atolls.json',
        'area' => 'Hectares',
    ],
    'islands' => $islands,
]);

/*
|--------------------------------------------------------------------------
| Cross-check inhabited islands with the Local Government Authority's councils
|--------------------------------------------------------------------------
*/

$councils = readJson("{$sources}/lga-councils.json")['councils'];
$council_islands = [];

foreach ($councils as $council) {
    if (! preg_match('/^([A-Za-z]+)\.\s+(.+?)\s+Council$/', $council['name'], $match)) {
        continue;
    }

    $council_islands[] = ['atoll' => $match[1], 'island' => $match[2], 'council' => $council['name']];
}

$normalize = fn (?string $name): string => strtolower(preg_replace('/[^a-z]/i', '', (string) $name));
$residential = array_filter($islands, fn (array $island) => $island['category'] === 'residential');

foreach ($council_islands as $council) {
    $atoll_code = strtolower($council['atoll']);

    $matches = array_filter($islands, fn (array $island) => strtolower($island['atoll']) === $atoll_code
        && $normalize($island['name']) === $normalize($council['island']));

    if ($matches === []) {
        $closest = null;
        $distance = PHP_INT_MAX;

        foreach ($residential as $island) {
            if (strtolower($island['atoll']) !== $atoll_code) {
                continue;
            }

            $candidate = levenshtein($normalize($island['name']), $normalize($council['island']));

            if ($candidate < $distance) {
                $closest = $island;
                $distance = $candidate;
            }
        }

        $suggestion = $closest ? ", probably {$closest['code']} `{$closest['name']}` ({$closest['dhivehi_name']})" : '';

        $review['councils'][] = "| {$council['council']} | No island in the register is spelled `{$council['island']}` in this atoll{$suggestion} |";

        continue;
    }

    $island = reset($matches);

    if ($island['category'] !== 'residential') {
        $review['councils'][] = "| {$council['council']} | The register lists {$island['code']} {$island['name']} as {$island['category']} |";
    }
}

$unnamed = count(array_filter($islands, fn (array $island) => $island['name'] === null));

/*
|--------------------------------------------------------------------------
| REVIEW.md
|--------------------------------------------------------------------------
*/

$lines = [
    '# Seed data for human review',
    '',
    'Generated by `php bin/build-data.php`. These are the parts of the seed data this package couldn\'t verify against an authoritative source. A Dhivehi speaker or someone who knows the registers should check them; fix a name in `database/data/sources/` (or `atolls.json`) and rebuild.',
    '',
    '## Atolls',
    '',
    '`atolls.json` is curated by hand. Its codes, letter codes and ISO 3166-2 codes are standard; please check:',
    '',
    '- `dhivehi_name`: the Thaana letter names of the atoll codes (ހާ އަލިފު, ށަވިޔަނި, …).',
    '- `official_dhivehi_name`: taken from the LGA\'s council names, which use the genitive (ތިލަދުންމަތީ އުތުރުބުރީ), and written here in the nominative (ތިލަދުންމަތީ އުތުރުބުރި). The English `official_name` spellings follow the Decentralization Act.',
    '',
    '## Dhivehi country names translated for this package',
    '',
    'Debian iso-codes has no (or a wrong) Dhivehi name for these countries:',
    '',
    '| Code | Country | Dhivehi | Why |',
    '| --- | --- | --- | --- |',
    ...($review['countries'] ?? []),
    '',
    'The other Dhivehi names come from iso-codes\' community translations (`dhivehi_name_source: iso-codes`). They were read through once, not checked one by one.',
    '',
    '## Countries without a calling code',
    '',
    'libphonenumber has no calling code for:',
    '',
    ...($review['calling_codes'] ?? []),
    '',
    '## Islands',
    '',
    "- {$unnamed} features in the register have no name. They're in `islands.json` with `name: null` and only seeded with `config('locations.seed.unnamed_islands')`.",
    '- Names are spelled as the register spells them (`Vilin\'gili`, `Hulhule`), which sometimes differs from the LGA\'s current spellings (`Vilingili`).',
    '',
    '| Code | Island | Note |',
    '| --- | --- | --- |',
    ...($review['islands'] ?? []),
    '',
    '## Councils of the Local Government Authority that don\'t match the register',
    '',
    'Each island council should govern a residential island of the register. These don\'t match by name (usually a spelling difference) or by category:',
    '',
    '| Council | Note |',
    '| --- | --- |',
    ...($review['councils'] ?? []),
    '',
];

file_put_contents("{$data}/REVIEW.md", implode("\n", $lines));
echo "Wrote REVIEW.md\n";
