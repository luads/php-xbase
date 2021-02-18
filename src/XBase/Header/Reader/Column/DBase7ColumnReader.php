<?php declare(strict_types=1);

namespace XBase\Header\Reader\Column;

use XBase\Column\DBase7Column;
use XBase\Stream\Stream;

class DBase7ColumnReader extends AbstractColumnReader
{
    public static function getColumnClass(): string
    {
        return DBase7Column::class;
    }

    public static function getHeaderLength(): int
    {
        return 48;
    }

    protected function extractArgs(string $memoryChunk): array
    {
        $s = Stream::createFromString($memoryChunk);

        return [
            $s->read(32),
            $s->read(), //type
            $s->readUChar(), //length
            $s->readUChar(), //decimalCount
            $s->readUShort(), //reserved1
            $s->readUChar(), //mdxFlag
            $s->readUShort(), //reserved2
            $s->readUInt(), //nextAI
            $s->read(4), //reserved3
        ];
    }
}
