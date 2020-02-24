<?php

namespace XBase\Column;

use XBase\Enum\TableType;

class ColumnFactory
{
    public static function getClass(string $version): string
    {
        switch ($version) {
            case TableType::DBASE_7_MEMO:
            case TableType::DBASE_7_NOMEMO:
                return DBase7Column::class;

            default:
                return DBaseColumn::class;
        }
    }
}
