<?php declare(strict_types=1);

namespace XBase\Header\Builder;

use XBase\Enum\TableType;
use XBase\Stream\Stream;

class HeaderBuilderFactory
{
    public static function create(string $filepath): HeaderBuilderInterface
    {
        $version = Stream::createFromFile($filepath)->readUChar();
        if (TableType::isVisualFoxpro($version)) {
            return new VisualFoxproBuilder($filepath);
        }

        switch ($version) {
            case TableType::DBASE_7_MEMO:
            case TableType::DBASE_7_NOMEMO:
                return new DBase7HeaderBuilder($filepath);
        }

        return new DBaseHeaderBuilder($filepath);
    }
}
