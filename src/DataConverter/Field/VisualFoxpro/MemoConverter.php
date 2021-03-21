<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\VisualFoxpro;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;

class MemoConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::MEMO;
    }

    public function fromBinaryString(string $value): ?int
    {
        $pointer = unpack('l', $value)[1];

        return $pointer ? (int) $pointer : null;
    }

    /**
     * @param int|null $value
     */
    public function toBinaryString($value): string
    {
        if (null === $value) {
            return pack('l', null);
        }

        return pack('l', $value);
    }
}
