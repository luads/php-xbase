<?php declare(strict_types=1);

namespace XBase\Record\DataConverter;

use XBase\Column\ColumnInterface;
use XBase\Enum\FieldType;
use XBase\Memo\MemoObject;

class FoxproDataConverter extends DBaseDataConverter
{
    protected function normalize(ColumnInterface $column, string $value)
    {
        switch ($column->getType()) {
            case FieldType::FLOAT:
                return $this->normalizeFloat($value);
            case FieldType::GENERAL:
                return $this->normalizeGeneral($value);
            default:
                return parent::normalize($column, $value);
        }
    }

    private function normalizeGeneral(string $value): ?MemoObject
    {
        return $this->table->getMemo()->get($value);
    }

    private function normalizeFloat(string $value): float
    {
        return (float) ltrim($value);
    }
}
