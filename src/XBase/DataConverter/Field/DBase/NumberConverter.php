<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase;

use XBase\Enum\FieldType;
use XBase\DataConverter\Field\AbstractFieldDataConverter;

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
        $value = null === $value ? '' : number_format($value, $this->column->getDecimalCount(), '.', '');
        return str_pad($value, $this->column->getLength(), ' ', STR_PAD_LEFT);
    }
}
