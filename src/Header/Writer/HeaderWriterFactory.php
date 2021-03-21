<?php declare(strict_types=1);

namespace XBase\Header\Writer;

use XBase\Enum\TableType;
use XBase\Stream\StreamWrapper;

class HeaderWriterFactory
{
    public static function create(int $version, StreamWrapper $fp): HeaderWriterInterface
    {
        $fp->seek(0);

        switch ($version) {
            case TableType::DBASE_7_MEMO:
            case TableType::DBASE_7_NOMEMO:
                return new DBase7HeaderWriter($fp);

            case TableType::VISUAL_FOXPRO:
            case TableType::VISUAL_FOXPRO_AI:
            case TableType::VISUAL_FOXPRO_VAR:
                return new VisualFoxproHeaderWriter($fp);

            default:
                return new DBaseHeaderWriter($fp);
        }
    }
}
