<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\VisualFoxpro;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;

class IntegerConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::INTEGER;
    }

    public function fromBinaryString(string $value): ?int
    {
        if ('' === ltrim($value, chr(0x00))) {
            return null;
        }

        $su = unpack('i', $value);

        return $su[1];
    }

    /**
     * @param int|null $value
     */
    public function toBinaryString($value): string
    {
        if (null === $value) {
            return str_pad('', $this->column->length, chr(0x00));
        }

        return pack('i', $value);
    }
}
