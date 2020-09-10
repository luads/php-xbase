<?php

namespace XBase\Column;

use XBase\Enum\FieldType;
use XBase\Stream\Stream;

class DBaseColumn extends AbstractColumn
{
    protected $reserved1;

    protected $reserved2;

    protected $reserved3;

    public static function getHeaderLength(): int
    {
        return 32;
    }

    public static function create(string $memoryChunk, int $colIndex, ?int $bytePos = null)
    {
        if (strlen($memoryChunk) !== self::getHeaderLength()) {
            throw new \LogicException('Column data expected length: '.self::getHeaderLength());
        }

        $s = Stream::createFromString($memoryChunk);

        return new static(
            $s->read(11),//0-10
            $s->read(),//11
            $s->readUInt(),//12-15
            $s->readUChar(),//16
            $s->readUChar(),//17
            $s->read(2),//18-19
            $s->readUChar(),//20
            $s->read(2),//21-22
            0 !== $s->readUChar(),//23
            $s->read(7),//24-30
            0 !== $s->readUChar(),//31
            $colIndex,
            $bytePos
        );
    }

    public function __construct(
        string $name,
        string $type,
        int $memAddress,
        int $length,
        int $decimalCount,
        $reserved1,
        int $workAreaID,
        $reserved2,
        bool $setFields,
        $reserved3,
        bool $indexed,
        int $colIndex,
        ?int $bytePos = null
    ) {
        $name = (false !== strpos($name, chr(0x00))) ? substr($name, 0, strpos($name, chr(0x00))) : $name;

        $this->rawName = $name;
        // chop all garbage from 0x00
        $this->name = strtolower($name);
        $this->type = $type;
        $this->memAddress = $memAddress;
        $this->length = $length;
        $this->decimalCount = $decimalCount;
        $this->reserved1 = $reserved1;
        $this->workAreaID = $workAreaID;
        $this->reserved2 = $reserved2;
        $this->setFields = $setFields;
        $this->reserved3 = $reserved3;
        $this->indexed = $indexed;
        $this->colIndex = $colIndex;
        $this->bytePos = $bytePos;
    }

    /**
     * @return int
     */
    public function getDataLength()
    {
        switch ($this->type) {
            case FieldType::DATE:
            case FieldType::DATETIME:
                return 8;
            case FieldType::LOGICAL:
                return 1;
            case FieldType::MEMO:
                return 10;
            default:
                return $this->length;
        }
    }

    /**
     * @return bool|string
     */
    public function toString()
    {
        return $this->name;
    }

    public function getReserved1()
    {
        return $this->reserved1;
    }

    public function getReserved2()
    {
        return $this->reserved2;
    }

    public function getReserved3()
    {
        return $this->reserved3;
    }
}
