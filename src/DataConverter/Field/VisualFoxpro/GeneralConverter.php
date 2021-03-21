<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\VisualFoxpro;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;

class GeneralConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::GENERAL;
    }

    public function fromBinaryString(string $value): int
    {
        $data = unpack('L', $value);

        return $data[1];
    }

    /**
     * @param int|null $value
     */
    public function toBinaryString($value): string
    {
        return pack('L', $value);
    }
}
