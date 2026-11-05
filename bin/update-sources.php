<?php

/**
 * Downloads the pinned source snapshots into database/data/sources.
 *
 *     php bin/update-sources.php
 *
 * Then run `php bin/build-data.php` and review the diff of database/data before committing.
 * Bump the versions below to update a source.
 */
const ISO_CODES_TAG = 'v4.20.1';
const LIBPHONENUMBER_TAG = 'v9.0.42';
const ONEMAP_ISLAND_LIST = 'https://readme.onemap.mv/csv/IslandList_20211101.csv';
const LGA_COUNCILS = 'https://www.lga.gov.mv/en/councils';

$sources = __DIR__ . '/../database/data/sources';

function download(string $url): string
{
    $context = stream_context_create(['http' => ['timeout' => 120, 'user_agent' => 'javaabu-locations-data-builder']]);
    $body = @file_get_contents($url, false, $context);

    if ($body === false) {
        fwrite(STDERR, "Could not download {$url}\n");
        exit(1);
    }

    return $body;
}

function save(string $path, string $contents): void
{
    file_put_contents($path, $contents);
    echo 'Saved ' . basename($path) . ' (' . strlen($contents) . " bytes)\n";
}

$iso_codes = 'https://salsa.debian.org/iso-codes-team/iso-codes/-/raw/' . ISO_CODES_TAG;

echo 'iso-codes ' . ISO_CODES_TAG . "\n";
save("{$sources}/iso_3166-1.json", download("{$iso_codes}/data/iso_3166-1.json"));
save("{$sources}/iso_3166-2.json", download("{$iso_codes}/data/iso_3166-2.json"));
save("{$sources}/iso_3166-1.dv.po", download("{$iso_codes}/iso_3166-1/dv.po"));

echo 'libphonenumber ' . LIBPHONENUMBER_TAG . "\n";
$metadata = download('https://raw.githubusercontent.com/google/libphonenumber/' . LIBPHONENUMBER_TAG . '/resources/PhoneNumberMetadata.xml');
preg_match_all('/<territory id="([A-Z]{2})"[^>]*?countryCode="(\d+)"/s', $metadata, $matches, PREG_SET_ORDER);

$calling_codes = [];

foreach ($matches as [, $region, $code]) {
    $calling_codes[$region] = $code;
}

ksort($calling_codes);

save("{$sources}/libphonenumber-calling-codes.json", json_encode([
    'source' => 'https://github.com/google/libphonenumber/blob/' . LIBPHONENUMBER_TAG . '/resources/PhoneNumberMetadata.xml',
    'licence' => 'Apache-2.0',
    'calling_codes' => $calling_codes,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo "OneMap island register\n";
save("{$sources}/onemap-IslandList_20211101.csv", download(ONEMAP_ISLAND_LIST));

echo "LGA councils\n";
// The page embeds its councils as JSON with escaped quotes (a backslash, then u0022)
$page = str_replace('\\' . 'u0022', '"', download(LGA_COUNCILS));
preg_match_all('/"council":\{"id":(\d+),"name":\{"en":"([^"]*)","dv":"([^"]*)"/', $page, $matches, PREG_SET_ORDER);

if ($matches === []) {
    fwrite(STDERR, 'No councils found on ' . LGA_COUNCILS . "; the page may have changed.\n");
    exit(1);
}

$decode = fn (string $value): string => trim((string) json_decode('"' . str_replace('\\\\', '\\', $value) . '"'));

$councils = [];

foreach ($matches as [, $id, $en, $dv]) {
    $councils[(int) $id] = [
        'id' => (int) $id,
        'name' => $decode($en),
        'dhivehi_name' => $decode($dv),
    ];
}

ksort($councils);

save("{$sources}/lga-councils.json", json_encode([
    'source' => LGA_COUNCILS,
    'retrieved' => date('Y-m-d'),
    'councils' => array_values($councils),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
