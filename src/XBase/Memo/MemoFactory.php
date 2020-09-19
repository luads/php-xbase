<?php

namespace XBase\Memo;

use XBase\Enum\TableType;
use XBase\Table;

class MemoFactory
{
    public static function create(Table $table): ?MemoInterface
    {
        $class = self::getClass($table->getVersion());
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

        return $refClass->newInstance($memoFilepath, $table->getConvertFrom());
    }

    private static function getClass(string $version): string
    {
        switch ($version) {
            case TableType::DBASE_III_PLUS_MEMO:
                return DBase3Memo::class;
            case TableType::DBASE_IV_MEMO:
            case TableType::DBASE_7_MEMO:
            case TableType::DBASE_7_NOMEMO:
                return DBase4Memo::class;
            case TableType::FOXPRO_MEMO:
                return FoxproMemo::class;
            case TableType::VISUAL_FOXPRO:
            case TableType::VISUAL_FOXPRO_AI:
            case TableType::VISUAL_FOXPRO_VAR:
                return VisualFoxproMemo::class;
        }
    }
}
