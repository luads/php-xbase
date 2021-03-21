<?php declare(strict_types=1);

namespace XBase\Header\Writer\Column;

use XBase\Enum\TableType;

class ColumnWriterFactory
{
    public static function create(int $version): ColumnWriterInterface
    {
        switch ($version) {
            case TableType::DBASE_7_MEMO:
            case TableType::DBASE_7_NOMEMO:
                return new DBase7ColumnWriter();

            default:
                return new DBaseColumnWriter();
        }
    }
}
