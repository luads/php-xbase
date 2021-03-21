<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase7;

use XBase\Enum\FieldType;

class AiConverter extends IntegerConverter
{
    public static function getType(): string
    {
        return FieldType::AUTO_INCREMENT;
    }
}
