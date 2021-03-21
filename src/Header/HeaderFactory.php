<?php declare(strict_types=1);

namespace XBase\Header;

use XBase\Enum\TableType;

class HeaderFactory
{
    public static function create(int $version): Header
    {
        if (!TableType::has($version)) {
            throw new \LogicException("Unknown table version $version");
        }

        $header = new Header();
        $header->version = $version;

        //add here specific options

        return $header;
    }
}
