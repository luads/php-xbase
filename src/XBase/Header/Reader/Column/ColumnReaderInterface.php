<?php declare(strict_types=1);

namespace XBase\Header\Reader\Column;

use XBase\Stream\StreamWrapper;

interface ColumnReaderInterface
{
    public static function getHeaderLength(): int;

    public static function getColumnClass(): string;

    public function read(StreamWrapper $fp, int $colIndex, ?int $bytePos = null);
}
