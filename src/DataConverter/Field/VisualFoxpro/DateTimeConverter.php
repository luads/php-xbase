<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\VisualFoxpro;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;

class DateTimeConverter extends AbstractFieldDataConverter
{
    const ZERO_DATE = 0x253d8c;

    const SEC_IN_DAY = 86400;

    public static function getType(): string
    {
        return FieldType::DATETIME;
    }

    public function fromBinaryString(string $value): ?\DateTimeInterface
    {
        if (empty($value)) {
            return null;
        }

        $buf = unpack('i*', $value);
        $intDate = $buf[1];
        $intTime = $buf[2];

        if (0 === $intDate && 0 === $intTime) {
            return null;
        }

        $longDate = ($intDate - self::ZERO_DATE) * self::SEC_IN_DAY;
        $ts = $longDate + intdiv($intTime, 1000);
        $ms = $intTime % 1000;

        return \DateTime::createFromFormat('U.u', "{$ts}.{$ms}");
    }

    /**
     * @param \DateTimeInterface|null $value
     */
    public function toBinaryString($value): string
    {
        if (null == $value) {
            return pack('i*', 0, 0);
        }

        $ts = (int) $value->format('U');
        $ms = (int) $value->format('v');

        $intTime = ($ts % self::SEC_IN_DAY);
        if ($intTime < 0) {
            $intTime += self::SEC_IN_DAY;
        }

        $intDate = ($ts - $intTime) / self::SEC_IN_DAY + self::ZERO_DATE;

        return pack('i*', $intDate, ($intTime * 1000 + $ms));
    }
}
