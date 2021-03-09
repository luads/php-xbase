<?php declare(strict_types=1);

namespace XBase\Memo\Creator;

use XBase\Enum\TableType;
use XBase\Table\Table;

final class MemoCreatorFactory
{
    public static function create(Table $table)
    {
        switch ($table->getVersion()) {
            case TableType::DBASE_III_PLUS_MEMO:
                return new DBase3MemoCreator($table);
            default:
                throw new \Exception('Memo creator not relized for table version '.$table->getVersion());
        }
    }
}
