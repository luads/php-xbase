<?php declare(strict_types=1);

namespace XBase\Header\Writer\Column;

use XBase\Header\Column;
use XBase\Stream\StreamWrapper;

interface ColumnWriterInterface
{
    public function write(StreamWrapper $fp, Column $column): void;
}
