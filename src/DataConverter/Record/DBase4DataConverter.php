<?php declare(strict_types=1);

namespace XBase\DataConverter\Record;

use XBase\DataConverter\Field\DBase4\BlobConverter;
use XBase\DataConverter\Field\DBase4\FloatConverter;
use XBase\DataConverter\Field\DBase4\OleConverter;

class DBase4DataConverter extends DBaseDataConverter
{
    protected static function getFieldConverters(): array
    {
        return array_merge([
            BlobConverter::class,
            FloatConverter::class,
            OleConverter::class,
        ], parent::getFieldConverters());
    }
}
