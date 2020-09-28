<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase7;

use XBase\DataConverter\Field\DBase\MemoConverter as DBaseMemoConverter;

class MemoConverter extends DBaseMemoConverter
{
    protected function getFillerChar(): string
    {
        return '0';
    }
}
