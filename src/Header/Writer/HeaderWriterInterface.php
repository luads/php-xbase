<?php declare(strict_types=1);

namespace XBase\Header\Writer;

use XBase\Header\Header;

interface HeaderWriterInterface
{
    public function write(Header $header): void;
}
