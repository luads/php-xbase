<?php

namespace XBase\Column;

use XBase\Enum\FieldType;
use XBase\Stream\Stream;

class DBaseColumn extends AbstractColumn
{
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

        return new self(
            $s->read(11),
            $s->read(),
            $s->readUInt(),
            $s->readUChar(),
            $s->readUChar(),
            $s->read(2),
            $s->readUChar(),
            $s->read(2),
            0 !== $s->read(),
            $s->read(7),
            0 !== $s->read(),
            $colIndex,
            $bytePos
        );
    }

    public function __construct(string $name, string $type, int $memAddress, int $length, int $decimalCount, $reserved1, int $workAreaID, $reserved2, bool $setFields, $reserved3, bool $indexed, int $colIndex, ?int $bytePos = null)
    {
        $name = (false !== strpos($name, chr(0x00))) ? substr($name, 0, strpos($name, chr(0x00))) : $name;

        $this->rawName = $name;
        // chop all garbage from 0x00
        $this->name = strtolower($name);
        $this->type = $type;
        $this->memAddress = $memAddress;
        $this->length = $length;
        $this->decimalCount = $decimalCount;
        $this->workAreaID = $workAreaID;
        $this->setFields = $setFields;
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
}
