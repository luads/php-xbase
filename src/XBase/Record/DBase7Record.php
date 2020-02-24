<?php

namespace XBase\Record;

use XBase\Column\ColumnInterface;
use XBase\Enum\FieldType;

class DBase7Record extends AbstractRecord
{
    /**
     * @var int Julian Day of Unix epoch start
     * @see https://planetcalc.com/503/
     */
    private const UTC_TO_JD = 0x42cc418ba99a00;

    private const SEC_TO_JD = 500;

    public function getObject(ColumnInterface $column)
    {
        switch ($column->getType()) {
            case FieldType::INTEGER:
            case FieldType::AUTO_INCREMENT:
                return $this->getInt($column->getName());
            case FieldType::TIMESTAMP:
                return $this->getTimestamp($column->getName());
            default:
                return parent::getObject($column);
        }
    }

    /**
     * Get DATE(D) or DATETIME(T) data as object of \DateTime class
     */
    public function getDateTimeObject(string $columnName): \DateTime
    {
        $column = $this->getColumn($columnName);
        if (!in_array($column->getType(), [FieldType::DATE, FieldType::TIMESTAMP])) {
            trigger_error($column->getName().' is not a Date or DateTime column', E_USER_ERROR);
        }

        $data = $this->forceGetString($columnName);
        if (in_array($column->getType(), [FieldType::TIMESTAMP])) {
            return \DateTime::createFromFormat('U', $this->getTimestamp($columnName));
        }

        return new \DateTime($data);
    }

    public function getTimestamp(string $columnName): int
    {
        $raw = $this->choppedData[$columnName];
        $buf = unpack('H14', $raw);
        return (hexdec($buf[1]) - self::UTC_TO_JD) / self::SEC_TO_JD;
    }

    /**
     * @todo This function should be optimized
     */
    public function getInt(string $columnName): int
    {
        $s = $this->choppedData[$columnName];
        //big endian
        $buf = unpack('C*', $s);
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
            $result .= $bin[$i] === '0' ? '1' : '0';
        }

        return $result;
    }
}
