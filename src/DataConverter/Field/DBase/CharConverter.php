<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;

class CharConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::CHAR;
    }

    public function fromBinaryString(string $value)
    {
        if ($inCharset = $this->table->options['encoding']) {
            $value = $this->encoder->encode($value, $inCharset, 'utf-8');
        }

        return trim($value);
    }

    public function toBinaryString($value): string
    {
        if ($value && $outCharset = $this->table->options['encoding']) {
            $value = $this->encoder->encode($value, 'utf-8', $outCharset);
        }

        return str_pad((string) $value, $this->column->length);
    }
}
