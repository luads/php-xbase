<?php declare(strict_types=1);

namespace XBase\Memo\Creator;

use XBase\Enum\TableType;
use XBase\Table\Table;

final class MemoCreatorFactory
{
    /**
     * @return MemoCreatorInterface
     */
    public static function create(Table $table)
    {
        switch ($table->getVersion()) {
            case TableType::DBASE_III_PLUS_MEMO:
                return new DBase3MemoCreator($table);
            case TableType::DBASE_IV_SQL_SYSTEM_MEMO:
            case TableType::DBASE_IV_SQL_TABLE_MEMO:
            case TableType::DBASE_IV_MEMO:
                return new DBase4MemoCreator($table);
            case TableType::DBASE_7_MEMO:
                return new DBase7MemoCreator($table);
            //todo foxpro
            default:
                throw new \Exception('Memo creator not realized for table version '.$table->getVersion());
        }
    }
}
