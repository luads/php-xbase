<?php declare(strict_types=1);

namespace XBase\Header\Builder;

use XBase\Header\DBaseHeader;

class DBaseHeaderBuilder extends AbstractHeaderBuilder
{
    protected function getClass(): string
    {
        return DBaseHeader::class;
    }
}
