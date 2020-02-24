<?php

namespace XBase\Record;

use XBase\Column\ColumnInterface;

interface RecordInterface
{
    public function isDeleted(): bool;

    public function getString(string $columnName);

    public function getObject(ColumnInterface $column);
}
