<?php declare(strict_types=1);

namespace XBase\Column;

use XBase\Enum\FieldType;
use XBase\Stream\Stream;
use XBase\Stream\StreamWrapper;

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
        string $rawName,
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
        $this->rawName = $rawName;
        $name = (false !== strpos($rawName, chr(0x00))) ? substr($rawName, 0, strpos($rawName, chr(0x00))) : trim($rawName);

        // chop all garbage from 0x00
        $this->name = strtolower($name);
        $this->type = $type;
        $this->memAddress = $memAddress;

        if (in_array($this->type, [FieldType::CHAR, FieldType::MEMO])) {
            $this->length = $length + 256 * $decimalCount;
        } else {
            $this->length = $length;
            $this->decimalCount = $decimalCount;
        }

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
     * @return bool|string
     */
    public function toString()
    {
        return $this->name;
    }

    public function toBinaryString(StreamWrapper $fp): void
    {
        $fp->write($this->rawName); // 0-10
        $fp->write($this->type); // 11
        $fp->writeUInt($this->memAddress); //12-15
        $fp->writeUChar($this->length); //16
        $fp->writeUChar($this->decimalCount); //17
        $fp->write($this->reserved1); //18-19
        $fp->writeUChar($this->workAreaID); //20
        $fp->write($this->reserved2); //21-22
        $fp->write(chr($this->setFields ? 1 : 0)); //23
        $fp->write($this->reserved3); //24-30
        $fp->write(chr($this->indexed ? 1 : 0)); //31
    }
}
