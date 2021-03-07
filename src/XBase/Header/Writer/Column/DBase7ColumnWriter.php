<?php declare(strict_types=1);

namespace XBase\Header\Writer\Column;

use XBase\Header\Column;
use XBase\Stream\StreamWrapper;

class DBase7ColumnWriter implements ColumnWriterInterface
{
    public function write(StreamWrapper $fp, Column $column): void
    {
        $fp->write($column->rawName);
        $fp->write($column->type);
        $fp->writeUChar($column->length);
        $fp->writeUChar($column->decimalCount);
        $fp->write(str_pad('', 2, chr(0)));
        $fp->writeUChar($column->mdxFlag);
        $fp->write(str_pad('', 2, chr(0)));
        $fp->writeInt($column->nextAI);
        $fp->write(str_pad('', 4, chr(0)));
    }
}
