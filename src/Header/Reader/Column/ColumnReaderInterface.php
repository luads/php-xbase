<?php declare(strict_types=1);

namespace XBase\Header\Reader\Column;

use XBase\Header\Column;
use XBase\Stream\StreamWrapper;

interface ColumnReaderInterface
{
    public function read(StreamWrapper $fp): Column;
}
