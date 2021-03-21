<?php declare(strict_types=1);

namespace XBase\DataConverter\Record;

use XBase\DataConverter\Field\Foxpro\FloatConverter;
use XBase\DataConverter\Field\Foxpro\GeneralConverter;

class FoxproDataConverter extends DBaseDataConverter
{
    protected static function getFieldConverters(): array
    {
        return array_merge([
            FloatConverter::class,
            GeneralConverter::class,
        ], parent::getFieldConverters());
    }
}
