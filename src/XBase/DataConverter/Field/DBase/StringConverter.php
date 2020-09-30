<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;

class StringConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::CHAR;
    }

    public function fromBinaryString(string $value)
    {
        if ($inCharset = $this->table->getConvertFrom()) {
            $value = iconv($inCharset, 'utf-8', $value);
        }

        return trim($value);
    }

    public function toBinaryString($value): string
    {
        if ($value && $outCharset = $this->table->getConvertFrom()) {
            $value = iconv('utf-8', $outCharset, $value);
        }

        return str_pad($value ?? '', $this->column->getLength());
    }
}
