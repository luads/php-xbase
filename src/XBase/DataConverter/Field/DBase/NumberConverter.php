<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;

class NumberConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::NUMERIC;
    }

    public function fromBinaryString(string $value)
    {
        $s = trim($value);
        if ('' === $s) {
            return null;
        }

        $s = str_replace(',', '.', $s);

        if ($this->column->getDecimalCount() > 0 || $this->column->getLength() > 9) {
            return (float) $s;
        }

        return (int) $s;
    }

    public function toBinaryString($value): string
    {
        if (null === $value) {
            return str_repeat(chr(0x00), $this->column->getLength());
        }

        return str_pad(
            number_format($value, $this->column->getDecimalCount(), '.', ''),
            $this->column->getLength(),
            ' ',
            STR_PAD_LEFT
        );
    }
}
