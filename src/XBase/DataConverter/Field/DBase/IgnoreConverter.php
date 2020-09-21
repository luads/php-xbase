<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase;

use XBase\Enum\FieldType;
use XBase\DataConverter\Field\AbstractFieldDataConverter;

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
        return $value ?? str_pad('', $this->column->getLength());
    }
}
