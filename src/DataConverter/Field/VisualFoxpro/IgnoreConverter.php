<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\VisualFoxpro;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;

class IgnoreConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::IGNORE;
    }

    public function fromBinaryString(string $value): string
    {
        return $value;
    }

    public function toBinaryString($value): string
    {
        return str_pad((string) $value, $this->column->length, chr(0x20));
    }
}
