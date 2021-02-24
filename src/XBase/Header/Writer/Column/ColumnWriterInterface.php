<?php declare(strict_types=1);

namespace XBase\Header\Writer\Column;

use XBase\Column\ColumnInterface;
use XBase\Stream\StreamWrapper;

interface ColumnWriterInterface
{
    public function write(StreamWrapper $fp, ColumnInterface $column): void;
}
