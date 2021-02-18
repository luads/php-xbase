<?php declare(strict_types=1);

namespace XBase\Header\Reader;

use XBase\Header\HeaderInterface;

interface HeaderReaderInterface
{
    /**
     * Reads data from file and build instance of HeaderInterface.
     */
    public function read(): HeaderInterface;
}
