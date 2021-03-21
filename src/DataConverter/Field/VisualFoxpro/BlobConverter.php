<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\VisualFoxpro;

use XBase\Enum\FieldType;

class BlobConverter extends MemoConverter
{
    public static function getType(): string
    {
        return FieldType::BLOB;
    }
}
