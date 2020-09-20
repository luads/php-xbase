<?php declare(strict_types=1);

namespace XBase\Record\DataConverter;

use XBase\Column\ColumnInterface;
use XBase\Enum\FieldType;
use XBase\Memo\MemoObject;

class DBase4DataConverter extends DBaseDataConverter
{
    const TYPE_BLOB = 'B';
    const TYPE_OLE  = 'G';

    protected function normalize(ColumnInterface $column, string $value)
    {
        switch ($column->getType()) {
            case FieldType::FLOAT:
                return $this->normalizeFloat($value);
            case self::TYPE_BLOB:
            case self::TYPE_OLE:
                return $this->normalizeBlob($value);
            default:
                return parent::normalize($column, $value);
        }
    }

    private function normalizeFloat(string $value): float
    {
        return (float) ltrim($value);
    }

    private function normalizeBlob(string $value): ?MemoObject
    {
        if (empty($pointer = ltrim($value, ' 0'))) {
            return null;
        }

        return $this->table->getMemo()->get($pointer);
    }
}
