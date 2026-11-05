<?php

namespace Javaabu\Locations\Seeding;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Javaabu\Locations\Data\LocationData;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Support\Translations;
use Javaabu\Translatable\Translatable;
use RuntimeException;

/**
 * Writes the package's seed data to the database, matching existing rows by their code
 *
 * Rows that exist are left alone unless `$update` is on, so names an app has edited survive
 * a re-seed. Nothing is ever deleted.
 */
class LocationsImporter
{
    public function __construct(
        protected bool $update = false,
    ) {}

    public function updatingExisting(bool $update = true): static
    {
        $this->update = $update;

        return $this;
    }

    public function countries(): ImportResult
    {
        $rows = LocationData::countries()->map(fn (array $country) => [
            'code' => $country['code'],
            'alpha3_code' => $country['alpha3_code'],
            'numeric_code' => $country['numeric_code'],
            'name' => $country['name'],
            'dhivehi_name' => $country['dhivehi_name'],
            'official_name' => $country['official_name'],
            'calling_code' => $country['calling_code'],
        ]);

        return $this->import('country', $rows);
    }

    public function atolls(): ImportResult
    {
        $rows = LocationData::atolls()->map(fn (array $atoll) => [
            'code' => $atoll['code'],
            'dhivehi_code' => $atoll['dhivehi_code'],
            'letter_code' => $atoll['letter_code'],
            'iso_code' => $atoll['iso_code'],
            'name' => $atoll['name'],
            'dhivehi_name' => $atoll['dhivehi_name'],
            'official_name' => $atoll['official_name'],
            'official_dhivehi_name' => $atoll['official_dhivehi_name'],
            'is_city' => $atoll['is_city'],
            'sort_order' => $atoll['sort_order'],
        ]);

        return $this->import('atoll', $rows);
    }

    /**
     * Islands of the register. Seed the atolls first.
     */
    public function islands(bool $include_unnamed = false): ImportResult
    {
        $atoll_ids = Locations::atollModel()::query()->pluck('id', 'code');

        $rows = LocationData::islands($include_unnamed)->map(function (array $island) use ($atoll_ids) {
            $atoll_id = $atoll_ids[$island['atoll']] ?? throw new RuntimeException("Seed the atolls before the islands: atoll [{$island['atoll']}] is missing.");

            return [
                'atoll_id' => $atoll_id,
                'code' => $island['code'],
                'name' => $island['name'],
                'dhivehi_name' => $island['dhivehi_name'],
                'category' => $island['category'],
                'is_capital' => $island['is_capital'],
                'latitude' => $island['latitude'],
                'longitude' => $island['longitude'],
                'area' => $island['area'],
            ];
        });

        return $this->import('island', $rows);
    }

    /**
     * Top-level ISO 3166-2 subdivisions. Seed the countries first.
     *
     * @param  array<int, string>|null  $countries  only these countries (alpha-2 codes)
     */
    public function states(?array $countries = null): ImportResult
    {
        $country_ids = Locations::countryModel()::query()->pluck('id', 'code');

        $rows = LocationData::states($countries)
            ->filter(fn (array $state) => isset($country_ids[$state['country']]))
            ->map(fn (array $state) => [
                'country_id' => $country_ids[$state['country']],
                'code' => $state['code'],
                'name' => $state['name'],
                'dhivehi_name' => null,
                'type' => $state['type'],
            ]);

        return $this->import('state', $rows);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    protected function import(string $type, Collection $rows): ImportResult
    {
        $model_class = Locations::model($type);

        /** @var Model $model */
        $model = new $model_class;
        $query = $model->newQuery();

        $existing = $query->clone()->pluck($model->getKeyName(), 'code');
        $translations = Translations::supports($model, 'name');
        $now = Date::now();

        $created = 0;
        $updated = 0;
        $inserts = [];

        foreach ($rows as $row) {
            if ($translations) {
                $row = [...$row, ...$this->translationColumns($row)];
            }

            $key = $existing[$row['code']] ?? null;

            if ($key === null) {
                $inserts[] = [...$row, 'created_at' => $now, 'updated_at' => $now];
                $created++;

                continue;
            }

            if (! $this->update) {
                continue;
            }

            $query->clone()->whereKey($key)->update([...$row, 'updated_at' => $now]);
            $updated++;
        }

        foreach (array_chunk($inserts, 200) as $chunk) {
            $query->clone()->insert($this->normalizeChunk($chunk));
        }

        return new ImportResult($type, $created, $updated, $rows->count() - $created - $updated);
    }

    /**
     * The JSON translations for javaabu/translatable: the English name is the primary one,
     * and each active Dhivehi locale gets the Dhivehi name
     *
     * @param  array<string, mixed>  $row
     * @return array{translations: string|null, lang: string}
     */
    protected function translationColumns(array $row): array
    {
        $translations = [];

        if (filled($row['dhivehi_name'] ?? null)) {
            foreach (Translatable::locales() as $locale) {
                if (! Locations::isDhivehiLocale($locale->code)) {
                    continue;
                }

                $translations[$locale->code] = ['name' => $row['dhivehi_name']];
            }
        }

        return [
            'translations' => $translations ? json_encode($translations, JSON_UNESCAPED_UNICODE) : null,
            'lang' => 'en',
        ];
    }

    /**
     * Give every row of a bulk insert the same columns
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeChunk(array $rows): array
    {
        $columns = array_fill_keys(array_keys(array_merge(...$rows)), null);

        return array_map(fn (array $row) => [...$columns, ...$row], $rows);
    }
}
