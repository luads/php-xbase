<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase7;

use XBase\Enum\FieldType;
use XBase\DataConverter\Field\AbstractFieldDataConverter;

class TimestampConverter extends AbstractFieldDataConverter
{
    /**
     * @var int Julian Day of Unix epoch start
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
        //todo
        throw new \Exception('NotRealized');
    }
}
