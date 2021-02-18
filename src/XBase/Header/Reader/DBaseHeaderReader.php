<?php declare(strict_types=1);

namespace XBase\Header\Reader;

use XBase\Header\DBaseHeader;

class DBaseHeaderReader extends AbstractHeaderReader
{
    protected function getClass(): string
    {
        return DBaseHeader::class;
    }
}
