<?php declare(strict_types=1);

namespace XBase\Record\DataConverter;

use XBase\Column\ColumnInterface;
use XBase\Enum\FieldType;

class DBase7DataConverter extends DBase4DataConverter
{
    /**
     * @var int Julian Day of Unix epoch start
     * @see https://planetcalc.com/503/
     */
    private const UTC_TO_JD = 0x42cc418ba99a00;

    private const SEC_TO_JD = 500;

    protected function normalize(ColumnInterface $column, string $value)
    {
        switch ($column->getType()) {
            case FieldType::INTEGER:
            case FieldType::AUTO_INCREMENT:
                return $this->normalizeInt($value);
            case FieldType::TIMESTAMP:
                return $this->normalizeTimestamp($value);
            default:
                return parent::normalize($column, $value);
        }
    }

    private function normalizeTimestamp(string $value): int
    {
        $buf = unpack('H14', $value);
        return (int) ((hexdec($buf[1]) - self::UTC_TO_JD) / self::SEC_TO_JD);
    }

    /**
     * @todo This function should be optimized
     */
    private function normalizeInt(string $value): int
    {
        //big endian
        $buf = unpack('C*', $value);
        $buf = array_map(function ($v) {
            return str_pad(decbin($v), 8, '0', STR_PAD_LEFT);
        }, $buf);
        // if first bit is 0 it is negative number
        $negative = '0' === $buf[1][0];
        $buf[1] = substr($buf[1], 1);

        if ($negative) {
            $buf = array_map(function ($v) {
                return $this->inverseBits($v);
            }, $buf);
        }

        $hex = array_reduce($buf, function ($c, $v) {
            return $c.str_pad(dechex(bindec($v)), 2, '0', STR_PAD_LEFT);
        }, '');

        $result = hexdec($hex);
        if ($negative) {
            $result = -($result + 1);
        }

        return $result;
    }

    private function inverseBits(string $bin): string
    {
        $len = strlen($bin);
        $result = '';
        for ($i = 0; $i < $len; $i++) {
            $result .= '0' === $bin[$i] ? '1' : '0';
        }

        return $result;
    }
}
