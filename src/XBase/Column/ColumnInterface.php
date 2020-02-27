<?php

namespace XBase\Column;

use XBase\Record;

interface ColumnInterface
{
    public static function getHeaderLength(): int;

    /**
     * @return ColumnInterface
     */
    public static function create(string $memoryChunk, int $colIndex, ?int $bytePos = null);

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
     * @deprecated use getMemAddress
     */
    public function getBytePos();

    public function getColIndex();
}
