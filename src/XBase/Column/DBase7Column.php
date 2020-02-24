<?php

namespace XBase\Column;

use XBase\Enum\FieldType;
use XBase\Record;
use XBase\Stream\Stream;

class DBase7Column extends AbstractColumn
{
    public static function getHeaderLength(): int
    {
        return 48;
    }

    public static function create(string $memoryChunk, int $colIndex, ?int $bytePos = null)
    {
        if (strlen($memoryChunk) !== self::getHeaderLength()) {
            throw new \LogicException('Column data expected length: '.self::getHeaderLength());
        }

        $s = Stream::createFromString($memoryChunk);

        return new self(
            $s->read(32),
            $s->read(1),
            $s->readUChar(),
            $s->readUChar(),
            $s->readUShort(),
            $s->readUChar(),
            $s->readUShort(),
            $s->readUInt(),
            $s->read(4),
            $colIndex,
            $bytePos
        );
    }

    /** @var int */
    protected $mdxFlag;
    /** @var int */
    protected $nextAI;

    /**
     * @var string $name         Field name in ASCII (zero-filled).
     * @var string $type         Field type in ASCII (B, C, D, N, L, M, @, I, +, F, 0 or G).
     * @var int    $length       Field length in binary.
     * @var int    $decimalCount Field decimal count in binary.
     * @var mixed  $reserved1    Reserved.
     * @var int    $mdxFlag      Production .MDX field flag; 0x01 if field has an index tag in the production .MDX file; 0x00 if the field is not indexed.
     * @var mixed  $reserved2    Reserved.
     * @var int    $nextAI       Next Autoincrement value, if the Field type is Autoincrement, 0x00 otherwise.
     * @var mixed  $reserved3    Reserved.
     */
    public function __construct(string $name, string $type, int $length, int $decimalCount, $reserved1, int $mdxFlag, $reserved2, int $nextAI, $reserved3, int $colIndex, ?int $bytePos = null)
    {
        $this->rawName = $name;
        $this->name = strtolower(rtrim($name, chr(0x00)));
        $this->type = $type;
        $this->length = $length;
        $this->decimalCount = $decimalCount;
        $this->mdxFlag = $mdxFlag;
        $this->nextAI = $nextAI;
        // not protocol
        $this->colIndex = $colIndex;
        $this->bytePos = $bytePos;
    }

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
}
