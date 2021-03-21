<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase7;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;

class IntegerConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::INTEGER;
    }

    /**
     * @todo This function should be optimized
     */
    public function fromBinaryString(string $value): int
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

//        $hex = array_reduce($buf, function ($c, $v) {
//            return $c.str_pad(dechex(bindec($v)), 2, '0', STR_PAD_LEFT);
//        }, '');
        $result = bindec(implode('', $buf));
//        $result = hexdec($hex);
        if ($negative) {
            $result = -($result + 1);
        }

        return $result;
    }

    /**
     * @param int $value
     */
    public function toBinaryString($value): string
    {
        if (is_null($value)) {
            $value = 0;
        }

        if ($negative = $value < 0) {
            $value = -($value + 1);
        }

        $buf = str_split(str_pad(decbin($value), 32, '0', STR_PAD_LEFT), 8);
        if ($negative) {
            $buf = array_map(function ($v) {
                return $this->inverseBits($v);
            }, $buf);
        }
        $buf[0][0] = $negative ? '0' : '1';

        return pack('C*', ...array_map('bindec', $buf));
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
