<?php

namespace XBase\Record;

use XBase\Enum\TableType;
use XBase\Table;

class RecordFactory
{
    public static function create(Table $table, int $recordIndex, $rawData = false): ?RecordInterface
    {
        $class = self::getClass($table->getVersion());
        $refClass = new \ReflectionClass($class);
        if (!$refClass->implementsInterface(RecordInterface::class)) {
            return null;
        }

        return $refClass->newInstance($table, $recordIndex, $rawData);
    }

    private static function getClass(string $version): string
    {
        switch ($version) {
            case TableType::DBASE_IV_MEMO:
                return DBase4Record::class;

            case TableType::DBASE_7_NOMEMO:
            case TableType::DBASE_7_MEMO:
                return DBase7Record::class;

            case TableType::FOXPRO_MEMO:
                return FoxproRecord::class;

            case TableType::VISUAL_FOXPRO:
            case TableType::VISUAL_FOXPRO_AI:
            case TableType::VISUAL_FOXPRO_VAR:
                return VisualFoxproRecord::class;

            case TableType::DBASE_III_PLUS_MEMO:
            case TableType::DBASE_III_PLUS_NOMEMO:
            default:
                return DBaseRecord::class;
        }
    }
}
