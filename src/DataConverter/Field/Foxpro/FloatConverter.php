<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\Foxpro;

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
        if (null !== $value) {
            $value = number_format($value, $this->column->decimalCount, '.', '');
        }

        return str_pad($value ?? '', $this->column->length, ' ', STR_PAD_LEFT);
    }
}
