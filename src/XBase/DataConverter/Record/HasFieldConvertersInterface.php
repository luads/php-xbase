<?php declare(strict_types=1);

namespace XBase\DataConverter\Record;

interface HasFieldConvertersInterface
{
    /**
     * @return array Class names which must implement FieldDataConverterInterface
     */
    public static function getFieldConverters(): array;
}
