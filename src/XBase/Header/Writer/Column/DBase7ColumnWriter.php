<?php declare(strict_types=1);

namespace XBase\Header\Writer\Column;

use XBase\Column\ColumnInterface;
use XBase\Stream\StreamWrapper;

class DBase7ColumnWriter implements ColumnWriterInterface
{
    public function write(StreamWrapper $fp, ColumnInterface $column): void
    {
        $fp->write($column->getRawName());
        $fp->write($column->getType());
        $fp->writeUChar($column->getLength());
        $fp->writeUChar($column->getDecimalCount());
        $fp->write(str_pad('', 2, chr(0)));
        $fp->writeUChar($column->getMdxFlag());
        $fp->write(str_pad('', 2, chr(0)));
        $fp->writeInt($column->getNextAI());
        $fp->write(str_pad('', 4, chr(0)));
    }
}
