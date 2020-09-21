<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase;

use XBase\Enum\FieldType;
use XBase\DataConverter\Field\AbstractFieldDataConverter;

class BlobConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::BLOB;
    }

    public function fromBinaryString(string $value): string
    {
        return trim($value);
    }

    public function toBinaryString($value): string
    {
        return str_pad($value ?? '', $this->column->getLength(), chr(0x00));
    }
}
