<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase4;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;

class BlobConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::DBASE4_BLOB; //blob
    }

    public function fromBinaryString(string $value): ?int
    {
        if (empty($pointer = ltrim($value, ' 0'))) {
            return null;
        }

        return (int) $pointer;
    }

    public function toBinaryString($value): string
    {
        $value = null === $value ? '' : (string) $value;

        return str_pad($value, $this->column->length, '0', STR_PAD_LEFT);
    }
}
