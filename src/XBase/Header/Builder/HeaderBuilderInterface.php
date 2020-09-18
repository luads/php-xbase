<?php declare(strict_types=1);

namespace XBase\Header\Builder;

use XBase\Header\HeaderInterface;

interface HeaderBuilderInterface
{
    public function build(): self;

    public function getHeader(): HeaderInterface;
}
