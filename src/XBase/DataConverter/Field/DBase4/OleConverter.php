<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase4;

class OleConverter extends BlobConverter
{
    public static function getType(): string
    {
        return 'G'; //OLE
    }
}
