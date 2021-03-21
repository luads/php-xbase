<?php declare(strict_types=1);

namespace XBase\Header\Column\Validator\DBase;

use XBase\Enum\FieldType;
use XBase\Header\Column;
use XBase\Header\Column\Validator\ColumnValidatorInterface;

class DateValidator implements ColumnValidatorInterface
{
    const LENGTH = 8;

    public function getType(): string
    {
        return FieldType::DATE;
    }

    public function validate(Column $column): void
    {
        $column->length = self::LENGTH;
    }
}
