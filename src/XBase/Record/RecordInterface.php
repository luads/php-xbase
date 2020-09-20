<?php

namespace XBase\Record;

use XBase\Column\ColumnInterface;

interface RecordInterface
{
    public function isDeleted(): bool;

    /**
     * Get column value
     */
    public function get(string $columnName);

    /**
     * Set column value
     */
    public function set(string $columnName, $value): self;

    /**
     * @deprecated since v1.3 and will be delete in v1.4. Use (string) $record->get('name')
     */
    public function getString(string $columnName);

    /**
     * @deprecated since v1.3 and will be delete in v1.4. Use (string) $record->get('name')
     */
    public function getObject(ColumnInterface $column);
}
