<?php declare(strict_types=1);

namespace XBase\Header\Reader\Column;

use XBase\Header\Column;
use XBase\Stream\StreamWrapper;

abstract class AbstractColumnReader implements ColumnReaderInterface
{
    public static function getHeaderLength(): int
    {
        return 32;
    }

    public function read(StreamWrapper $fp): Column
    {
        $memoryChunk = $fp->read(static::getHeaderLength());
        if (($len = strlen($memoryChunk)) !== static::getHeaderLength()) {
            throw new \LogicException('Column data expected length: '.static::getHeaderLength().' got: '.$len);
        }

        return $this->createColumn($memoryChunk);
    }

    protected function createColumn(string $memoryChunk): Column
    {
        $properties = $this->extractArgs($memoryChunk);

        $column = new Column();
        foreach ($properties as $property => $value) {
            $column->{$property} = $value;
        }

        return $column;
    }

    abstract protected function extractArgs(string $memoryChunk): array;
}
