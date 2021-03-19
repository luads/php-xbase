<?php declare(strict_types=1);

namespace XBase\Header\Column\Validator\DBase7;

use XBase\Enum\FieldType;
use XBase\Header\Column\Validator\DBase\DateValidator;

class TimestampValidator extends DateValidator
{
    public function getType(): string
    {
        return FieldType::TIMESTAMP;
    }
}
