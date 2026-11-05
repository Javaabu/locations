<?php

/**
 * Checks the seed data in database/data: shapes, codes, uniqueness, references, Thaana
 * names and coordinates. CI runs it, and so should you after changing the data.
 *
 *     php bin/validate-data.php
 */
$data = __DIR__ . '/../database/data';
$errors = [];

function dataFile(string $path): array
{
    return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
}

/** Thaana letters and marks, spaces, and the punctuation names use */
function isThaana(?string $value): bool
{
    return $value === null || preg_match('/^[\x{0780}-\x{07BF}\x{060C} ().\-]+$/u', $value) === 1;
}

/**
 * @param  array<int, array<string, mixed>>  $rows
 */
function checkUnique(array $rows, string $field, string $label, array &$errors): void
{
    $values = array_filter(array_column($rows, $field), fn ($value) => $value !== null);

    foreach (array_count_values($values) as $value => $count) {
        if ($count > 1) {
            $errors[] = "{$label}: {$field} {$value} appears {$count} times";
        }
    }
}

// Countries
$countries = dataFile("{$data}/countries.json")['countries'];

if (count($countries) !== 249) {
    $errors[] = 'countries: expected the 249 ISO 3166-1 countries, found ' . count($countries);
}

foreach (['code', 'alpha3_code', 'numeric_code'] as $field) {
    checkUnique($countries, $field, 'countries', $errors);
}

foreach ($countries as $country) {
    $label = "countries.{$country['code']}";

    if (! preg_match('/^[A-Z]{2}$/', $country['code'])) {
        $errors[] = "{$label}: invalid alpha-2 code";
    }

    if (! preg_match('/^[A-Z]{3}$/', $country['alpha3_code'])) {
        $errors[] = "{$label}: invalid alpha-3 code";
    }

    if (! preg_match('/^\d{3}$/', $country['numeric_code'])) {
        $errors[] = "{$label}: invalid numeric code";
    }

    if (trim((string) $country['name']) === '') {
        $errors[] = "{$label}: no name";
    }

    if (! $country['dhivehi_name'] || ! isThaana($country['dhivehi_name'])) {
        $errors[] = "{$label}: missing or non-Thaana Dhivehi name";
    }

    if ($country['calling_code'] !== null && ! preg_match('/^\d{1,4}$/', $country['calling_code'])) {
        $errors[] = "{$label}: invalid calling code";
    }
}

$country_codes = array_column($countries, 'code');

// Atolls
$atolls = dataFile("{$data}/atolls.json")['atolls'];

if (count($atolls) !== 21) {
    $errors[] = 'atolls: expected 20 administrative atolls and Malé City, found ' . count($atolls);
}

foreach (['code', 'letter_code', 'iso_code', 'sort_order', 'register_name'] as $field) {
    checkUnique($atolls, $field, 'atolls', $errors);
}

foreach ($atolls as $atoll) {
    $label = "atolls.{$atoll['code']}";

    if (! preg_match('/^[A-Z]$/', (string) $atoll['letter_code'])) {
        $errors[] = "{$label}: invalid letter code";
    }

    if (! preg_match('/^MV-(\d{2}|MLE)$/', (string) $atoll['iso_code'])) {
        $errors[] = "{$label}: invalid ISO 3166-2 code";
    }

    foreach (['dhivehi_code', 'dhivehi_name', 'official_dhivehi_name'] as $field) {
        if (! isThaana($atoll[$field])) {
            $errors[] = "{$label}: {$field} isn't Thaana";
        }
    }

    foreach (['name', 'dhivehi_name', 'official_name', 'official_dhivehi_name'] as $field) {
        if (! $atoll[$field]) {
            $errors[] = "{$label}: no {$field}";
        }
    }
}

$atoll_codes = array_column($atolls, 'code');

// Islands
$islands = dataFile("{$data}/islands.json")['islands'];
$categories = ['residential', 'tourism', 'industrial', 'institutional', 'uninhabited'];
$capitals = [];

checkUnique($islands, 'code', 'islands', $errors);

foreach ($islands as $island) {
    $label = "islands.{$island['code']}";

    if (! preg_match('/^LD\d{4}$/', $island['code'])) {
        $errors[] = "{$label}: invalid feature code";
    }

    if (! in_array($island['atoll'], $atoll_codes, true)) {
        $errors[] = "{$label}: unknown atoll {$island['atoll']}";
    }

    if (! in_array($island['category'], $categories, true)) {
        $errors[] = "{$label}: unknown category {$island['category']}";
    }

    if (! isThaana($island['dhivehi_name'])) {
        $errors[] = "{$label}: Dhivehi name isn't Thaana";
    }

    if (($island['name'] === null) !== ($island['dhivehi_name'] === null)) {
        $errors[] = "{$label}: has a name in only one language";
    }

    if ($island['latitude'] === null || $island['latitude'] < -1 || $island['latitude'] > 8) {
        $errors[] = "{$label}: latitude outside the Maldives";
    }

    if ($island['longitude'] === null || $island['longitude'] < 72 || $island['longitude'] > 74) {
        $errors[] = "{$label}: longitude outside the Maldives";
    }

    if ($island['is_capital']) {
        $capitals[$island['atoll']][] = $island['code'];
    }
}

foreach ($atoll_codes as $code) {
    $count = count($capitals[$code] ?? []);

    if ($count !== 1) {
        $errors[] = "islands: atoll {$code} has {$count} capitals";
    }
}

// States
$states = dataFile("{$data}/states.json")['states'];

checkUnique($states, 'code', 'states', $errors);

foreach ($states as $state) {
    $label = "states.{$state['code']}";

    if (! preg_match('/^[A-Z]{2}-[A-Z0-9]{1,3}$/', $state['code'])) {
        $errors[] = "{$label}: invalid ISO 3166-2 code";
    }

    if (! in_array($state['country'], $country_codes, true)) {
        $errors[] = "{$label}: unknown country {$state['country']}";
    }

    if ($state['country'] === 'MV') {
        $errors[] = "{$label}: Maldivian subdivisions are atolls, not states";
    }

    if (trim((string) $state['name']) === '') {
        $errors[] = "{$label}: no name";
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode("\n", $errors) . "\n");
    fwrite(STDERR, count($errors) . " problems in the seed data.\n");
    exit(1);
}

echo sprintf(
    "Seed data OK: %d countries, %d atolls, %d islands (%d named), %d states.\n",
    count($countries),
    count($atolls),
    count($islands),
    count(array_filter($islands, fn (array $island) => $island['name'] !== null)),
    count($states),
);
