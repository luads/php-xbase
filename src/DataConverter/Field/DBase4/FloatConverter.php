<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase4;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;

class FloatConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::FLOAT;
    }

    public function fromBinaryString(string $value): float
    {
        return (float) ltrim($value);
    }

    public function toBinaryString($value): string
    {
        return str_pad((string) ($value ?? ''), $this->column->length);
    }
}
