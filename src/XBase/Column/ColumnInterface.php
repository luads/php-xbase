<?php declare(strict_types=1);

namespace XBase\Column;

use XBase\Stream\StreamWrapper;

interface ColumnInterface
{
    public static function getHeaderLength(): int;

    /**
     * @return ColumnInterface
     */
    public static function create(string $memoryChunk, int $colIndex, ?int $bytePos = null);

    public function toBinaryString(StreamWrapper $fp): void;

    public function getDecimalCount();

    /**
     * @return bool
     */
    public function isIndexed();

    /**
     * @return int
     */
    public function getLength();

    /**
     * @return int
     */
    public function getMemAddress();

    /**
     * @return bool|string
     */
    public function getName();

    public function isSetFields();

    public function getType();

    public function getWorkAreaID();

    /**
     * @return int
     */
    public function getBytePos();

    public function getColIndex();
}
