<?php declare(strict_types=1);

namespace XBase\Header\Writer\Column;

use XBase\Header\Column;
use XBase\Stream\StreamWrapper;

class DBaseColumnWriter implements ColumnWriterInterface
{
    const RAW_NAME_MAX_LENGTH = 11;

    public function write(StreamWrapper $fp, Column $column): void
    {
        $chr0 = chr(0);
        $rawName = $column->rawName ?? str_pad($column->name, self::RAW_NAME_MAX_LENGTH, $chr0);
        if (empty($rawName)) {
            throw new \LogicException('Column rawName is not defined');
        }

        $fp->write(substr($rawName, 0, self::RAW_NAME_MAX_LENGTH)); // 0-10
        $fp->write($column->type); // 11
        $fp->writeUInt($column->memAddress); //12-15
        $fp->writeUChar($column->length); //16
        $fp->writeUChar($column->decimalCount); //17
        $fp->write(str_pad($column->reserved1 ?? '', 2, $chr0)); //18-19
        $fp->writeUChar($column->workAreaID); //20
        $fp->write(str_pad($column->reserved2 ?? '', 2, $chr0)); //21-22
        $fp->write(chr($column->setFields ? 1 : 0)); //23
        $fp->write(str_pad($column->reserved3 ?? '', 7, $chr0)); //24-30
        $fp->write(chr($column->indexed ? 1 : 0)); //31
    }
}
