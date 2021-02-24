<?php declare(strict_types=1);

namespace XBase\Header\Writer\Column;

use XBase\Column\ColumnInterface;
use XBase\Stream\StreamWrapper;

class DBaseColumnWriter implements ColumnWriterInterface
{
    public function write(StreamWrapper $fp, ColumnInterface $column): void
    {
        $fp->write($column->getRawName()); // 0-10
        $fp->write($column->getType()); // 11
        $fp->writeUInt($column->getMemAddress()); //12-15
        $fp->writeUChar($column->getLength()); //16
        $fp->writeUChar($column->getDecimalCount()); //17
        $fp->write($column->getReserved1()); //18-19
        $fp->writeUChar($column->getWorkAreaID()); //20
        $fp->write($column->getReserved2()); //21-22
        $fp->write(chr($column->isSetFields() ? 1 : 0)); //23
        $fp->write($column->getReserved3()); //24-30
        $fp->write(chr($column->isIndexed() ? 1 : 0)); //31
    }
}
