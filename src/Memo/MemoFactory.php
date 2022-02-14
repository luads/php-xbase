<?php declare(strict_types=1);

namespace XBase\Memo;

use XBase\DataConverter\Encoder\EncoderInterface;
use XBase\Enum\TableType;
use XBase\Table\Table;

class MemoFactory
{
    public static function create(Table $table, EncoderInterface $encoder): ?MemoInterface
    {
        $class = self::getClass($table->getVersion());
        $refClass = new \ReflectionClass($class);
        if (!$refClass->implementsInterface(MemoInterface::class)) {
            return null;
        }

        $memoExt = $refClass->getMethod('getExtension')->invoke(null);
        $fileInfo = pathinfo($table->filepath);
        // if file extension in UPPERCASE then memo file extension should be in upper case too
        $memoExt = 'DBF' === ($fileInfo['extension'] ?? null) ? strtoupper($memoExt) : $memoExt;
        if ('.' !== substr($memoExt, 0, 1)) {
            $memoExt = '.'.$memoExt;
        }
        $memoFilepath = $fileInfo['dirname'].DIRECTORY_SEPARATOR.$fileInfo['filename'].$memoExt;
        if (!file_exists($memoFilepath)) {
            return null; //todo create file?
        }

        return $refClass->newInstance($table, $memoFilepath, $encoder);
    }

    private static function getClass(int $version): string
    {
        switch ($version) {
            case TableType::DBASE_III_PLUS_MEMO:
                return DBase3Memo::class;
            case TableType::DBASE_IV_MEMO:
                return DBase4Memo::class;
            case TableType::DBASE_7_MEMO:
            case TableType::DBASE_7_NOMEMO:
                return DBase7Memo::class;
            case TableType::FOXPRO_MEMO:
            case TableType::VISUAL_FOXPRO:
            case TableType::VISUAL_FOXPRO_AI:
            case TableType::VISUAL_FOXPRO_VAR:
                return FoxproMemo::class;
        }

        throw new \LogicException('Unknown table memo type: '.$version);
    }
}
