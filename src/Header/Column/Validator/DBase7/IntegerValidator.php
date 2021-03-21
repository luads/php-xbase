<?php declare(strict_types=1);

namespace XBase\Header\Column\Validator\DBase7;

use XBase\Enum\FieldType;
use XBase\Header\Column;
use XBase\Header\Column\Validator\ColumnValidatorInterface;

class IntegerValidator implements ColumnValidatorInterface
{
    const LENGTH = 4;

    public function getType(): array
    {
        return [
            FieldType::AUTO_INCREMENT,
            FieldType::INTEGER,
        ];
    }

    public function validate(Column $column): void
    {
        $column->length = self::LENGTH;
    }
}
