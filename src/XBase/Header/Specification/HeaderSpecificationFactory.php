<?php declare(strict_types=1);

namespace XBase\Header\Specification;

use XBase\Enum\TableType;

class HeaderSpecificationFactory
{
    public static function create(int $version = TableType::DBASE_II): Specification
    {
        $spec = new Specification();

        switch ($version) {
            case TableType::DBASE_7_MEMO:
            case TableType::DBASE_7_NOMEMO:
                $spec->headerTopLength = 68; // 32 + [Language driver name](32) + [Reserved](4) +
                $spec->fieldLength = 48;
                break;
        }

        return $spec;
    }
}
