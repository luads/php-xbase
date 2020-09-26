<?php

namespace XBase\Memo;

use XBase\Enum\TableType;
use XBase\Table;
use XBase\Writable\Memo\WritableFoxproMemo;

class MemoFactory
{
    public static function create(Table $table, bool $writable = false): ?MemoInterface
    {
        $class = self::getClass($table->getVersion(), $writable);
        $refClass = new \ReflectionClass($class);
        if (!$refClass->implementsInterface(MemoInterface::class)) {
            return null;
        }

        $memoExt = $refClass->getMethod('getExtension')->invoke(null);
        $fileInfo = pathinfo($table->getFilepath());
        $memoExt = 'DBF' === $fileInfo['extension'] ? strtoupper($memoExt) : $memoExt;
        if ('.' !== substr($memoExt, 0, 1)) {
            $memoExt = '.'.$memoExt;
        }
        $memoFilepath = $fileInfo['dirname'].DIRECTORY_SEPARATOR.$fileInfo['filename'].$memoExt;
        if (!file_exists($memoFilepath)) {
            return null; //todo create file?
        }

        return $refClass->newInstance($table, $memoFilepath, $table->getConvertFrom());
    }

    private static function getClass(string $version, bool $writable): string
    {
        if ($writable){
            return self::getWritableClass($version);
        }

        switch ($version) {
            case TableType::DBASE_III_PLUS_MEMO:
                return DBase3Memo::class;
            case TableType::DBASE_IV_MEMO:
            case TableType::DBASE_7_MEMO:
            case TableType::DBASE_7_NOMEMO:
                return DBase4Memo::class;
            case TableType::FOXPRO_MEMO:
            case TableType::VISUAL_FOXPRO:
            case TableType::VISUAL_FOXPRO_AI:
            case TableType::VISUAL_FOXPRO_VAR:
                return FoxproMemo::class;
        }

        throw new \LogicException('Unknown table memo type: '.$version);
    }

    private static function getWritableClass(string $version): string
    {
        switch ($version) {
            case TableType::FOXPRO_MEMO:
            case TableType::VISUAL_FOXPRO:
            case TableType::VISUAL_FOXPRO_AI:
            case TableType::VISUAL_FOXPRO_VAR:
                return WritableFoxproMemo::class;
        }

        throw new \LogicException('Unknown table memo type: '.$version);
    }
}
