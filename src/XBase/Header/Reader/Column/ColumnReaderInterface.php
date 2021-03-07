<?php declare(strict_types=1);

namespace XBase\Header\Reader\Column;

use XBase\Header\Column;
use XBase\Stream\StreamWrapper;

interface ColumnReaderInterface
{
    public static function getHeaderLength(): int;

    public function read(StreamWrapper $fp): Column;
}
