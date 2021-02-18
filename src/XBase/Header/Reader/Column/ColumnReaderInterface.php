<?php declare(strict_types=1);

namespace XBase\Header\Reader\Column;

interface ColumnReaderInterface
{
    public static function getHeaderLength(): int;

    public static function getColumnClass(): string;

    public function read(string $memoryChunk, int $colIndex, ?int $bytePos = null);
}
