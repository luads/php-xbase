<?php declare(strict_types=1);

namespace XBase\DataConverter\Record;

use XBase\DataConverter\Field\VisualFoxpro\BlobConverter;
use XBase\DataConverter\Field\VisualFoxpro\CurrencyConverter;
use XBase\DataConverter\Field\VisualFoxpro\DateTimeConverter;
use XBase\DataConverter\Field\VisualFoxpro\DoubleConverter;
use XBase\DataConverter\Field\VisualFoxpro\GeneralConverter;
use XBase\DataConverter\Field\VisualFoxpro\IgnoreConverter;
use XBase\DataConverter\Field\VisualFoxpro\IntegerConverter;
use XBase\DataConverter\Field\VisualFoxpro\MemoConverter;
use XBase\DataConverter\Field\VisualFoxpro\VarBinaryConverter;
use XBase\DataConverter\Field\VisualFoxpro\VarFieldConverter;

class VisualFoxproDataConverter extends FoxproDataConverter
{
    protected static function getFieldConverters(): array
    {
        return array_merge([
            BlobConverter::class,
            CurrencyConverter::class,
            DateTimeConverter::class,
            DoubleConverter::class,
            GeneralConverter::class,
            IgnoreConverter::class,
            IntegerConverter::class,
            MemoConverter::class,
            VarFieldConverter::class,
            VarBinaryConverter::class,
        ], parent::getFieldConverters());
    }
}
