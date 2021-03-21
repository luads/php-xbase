<?php declare(strict_types=1);

namespace XBase\Header\Reader;

use XBase\Header\Header;

interface HeaderReaderInterface
{
    /**
     * Reads data from file and build instance of Header.
     */
    public function read(): Header;
}
