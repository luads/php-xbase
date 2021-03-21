<?php declare(strict_types=1);

namespace XBase\Header\Column\Validator\DBase4;

use XBase\Enum\FieldType;
use XBase\Header\Column\Validator\DBase\NumberValidator;

class FloatValidator extends NumberValidator
{
    public function getType(): string
    {
        return FieldType::FLOAT;
    }
}
