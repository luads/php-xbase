<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase4;

use XBase\Memo\MemoObject;
use XBase\DataConverter\Field\AbstractFieldDataConverter;

class BlobConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return 'B'; //blob
    }

    public function fromBinaryString(string $value): ?MemoObject
    {
        if (empty($pointer = ltrim($value, ' 0'))) {
            return null;
        }

        return $this->table->getMemo()->get($pointer);
    }

    public function toBinaryString($value): string
    {
        return str_pad($value ?? '', $this->column->getLength(), chr(0x00));
    }
}
