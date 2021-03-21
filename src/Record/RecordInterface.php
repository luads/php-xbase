<?php declare(strict_types=1);

namespace XBase\Record;

interface RecordInterface
{
    /**
     * @return int zero based row index
     */
    public function getRecordIndex(): int;

    public function isDeleted(): bool;

    /**
     * Get column value.
     */
    public function get(string $columnName);

    public function getGenuine(string $columnName);

    /**
     * Set column value.
     */
    public function set(string $columnName, $value): self;

    public function setGenuine(string $columnName, $value);
}
