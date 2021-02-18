<?php declare(strict_types=1);

namespace XBase\Header\Reader\Column;

use XBase\Stream\Stream;

abstract class AbstractColumnReader implements ColumnReaderInterface
{
    public static function getHeaderLength(): int
    {
        return 32;
    }

    public function read(string $memoryChunk, int $colIndex, ?int $bytePos = null)
    {
        if (($len = strlen($memoryChunk)) !== static::getHeaderLength()) {
            throw new \LogicException('Column data expected length: '.static::getHeaderLength().' got: '.$len);
        }

        $args = $this->extractArgs($memoryChunk);
        array_push($args, $colIndex, $bytePos);
        array_push($args, $bytePos);

        $refClass = new \ReflectionClass(static::getColumnClass());

        return $refClass->newInstanceArgs($args);
    }

    protected function extractArgs(string $memoryChunk): array
    {
        $s = Stream::createFromString($memoryChunk);

        return [
            $s->read(11), //0-10
            $s->read(), //11
            $s->readUInt(), //12-15
            $s->readUChar(), //16
            $s->readUChar(), //17
            $s->read(2), //18-19
            $s->readUChar(), //20
            $s->read(2), //21-22
            0 !== $s->readUChar(), //23
            $s->read(7), //24-30
            0 !== $s->readUChar(), //31
        ];
    }
}
