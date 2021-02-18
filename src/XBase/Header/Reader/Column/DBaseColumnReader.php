<?php declare(strict_types=1);

namespace XBase\Header\Reader\Column;

use XBase\Column\DBaseColumn;

class DBaseColumnReader extends AbstractColumnReader
{
    public static function getColumnClass(): string
    {
        return DBaseColumn::class;
    }
}
