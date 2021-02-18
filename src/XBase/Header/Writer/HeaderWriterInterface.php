<?php declare(strict_types=1);

namespace XBase\Header\Writer;

use XBase\Header\HeaderInterface;

interface HeaderWriterInterface
{
    public function write(HeaderInterface $header): void;
}
