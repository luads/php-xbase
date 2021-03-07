<?php declare(strict_types=1);

namespace XBase\Header\Writer\Column;

use XBase\Header\Column;
use XBase\Stream\StreamWrapper;

class DBaseColumnWriter implements ColumnWriterInterface
{
    public function write(StreamWrapper $fp, Column $column): void
    {
        $fp->write($column->rawName); // 0-10
        $fp->write($column->type); // 11
        $fp->writeUInt($column->memAddress); //12-15
        $fp->writeUChar($column->length); //16
        $fp->writeUChar($column->decimalCount); //17
        $fp->write($column->reserved1); //18-19
        $fp->writeUChar($column->workAreaID); //20
        $fp->write($column->reserved2); //21-22
        $fp->write(chr($column->setFields ? 1 : 0)); //23
        $fp->write($column->reserved3); //24-30
        $fp->write(chr($column->indexed ? 1 : 0)); //31
    }
}
