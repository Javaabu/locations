<?php

namespace Javaabu\Locations\Seeding;

/**
 * What an import did for one location type
 */
final readonly class ImportResult
{
    public function __construct(
        public string $type,
        public int $created,
        public int $updated,
        public int $skipped,
    ) {}

    public function total(): int
    {
        return $this->created + $this->updated + $this->skipped;
    }
}
