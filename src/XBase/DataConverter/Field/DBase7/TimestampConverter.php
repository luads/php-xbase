<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase7;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;

class TimestampConverter extends AbstractFieldDataConverter
{
    /**
     * @var int Julian Day of Unix epoch start
     *
     * @see https://planetcalc.com/503/
     */
    private const UTC_TO_JD = 0x42cc418ba99a00;

    private const SEC_TO_JD = 500;

    public static function getType(): string
    {
        return FieldType::TIMESTAMP;
    }

    public function fromBinaryString(string $value): int
    {
        $buf = unpack('H14', $value);

        return (int) ((hexdec($buf[1]) - self::UTC_TO_JD) / self::SEC_TO_JD);
    }

    public function toBinaryString($value): string
    {
        $hex = dechex($value * self::SEC_TO_JD + self::UTC_TO_JD);

        return pack('H16', $hex.'00');
    }
}
