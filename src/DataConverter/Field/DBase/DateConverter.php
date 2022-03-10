<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;

class DateConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::DATE;
    }

    public function fromBinaryString(string $value): ?string
    {
        return $value;
    }

    public function toBinaryString($value): string
    {
        assert(null === $value || 8 === strlen($value));
        return null === $value ? str_pad(chr(0x00), $this->column->length) : $value;
    }
}
