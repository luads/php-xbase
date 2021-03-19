<?php declare(strict_types=1);

namespace XBase\Header\Writer\Column;

use XBase\Header\Column;
use XBase\Stream\StreamWrapper;

class DBase7ColumnWriter implements ColumnWriterInterface
{
    const RAW_NAME_MAX_LENGTH = 32;

    public function write(StreamWrapper $fp, Column $column): void
    {
        $chr0 = chr(0);
        $rawName = $column->rawName ?? str_pad($column->name, self::RAW_NAME_MAX_LENGTH, $chr0);
        if (empty($rawName)) {
            throw new \LogicException('Column rawName is not defined');
        }

        $fp->write($rawName);
        $fp->write($column->type);
        $fp->writeUChar($column->length);
        $fp->writeUChar($column->decimalCount);
        $fp->write(str_pad($column->reserved1 ?? '', 2, $chr0));
        $fp->writeUChar($column->mdxFlag ?? 0);
        $fp->write(str_pad($column->reserved1 ?? '', 2, $chr0));
        $fp->writeInt($column->nextAI ?? 0);
        $fp->write(str_pad($column->reserved1 ?? '', 4, $chr0));
    }
}
