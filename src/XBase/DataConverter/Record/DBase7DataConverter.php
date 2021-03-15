<?php declare(strict_types=1);

namespace XBase\DataConverter\Record;

use XBase\DataConverter\Field\DBase7\AiConverter;
use XBase\DataConverter\Field\DBase7\DoubleConverter;
use XBase\DataConverter\Field\DBase7\IntegerConverter;
use XBase\DataConverter\Field\DBase7\MemoConverter;
use XBase\DataConverter\Field\DBase7\TimestampConverter;

class DBase7DataConverter extends DBase4DataConverter
{
    protected static function getFieldConverters(): array
    {
        return array_merge([
            AiConverter::class,
            DoubleConverter::class,
            IntegerConverter::class,
            TimestampConverter::class,
            MemoConverter::class,
        ], parent::getFieldConverters());
    }
}
