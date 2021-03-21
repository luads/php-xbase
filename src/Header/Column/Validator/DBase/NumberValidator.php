<?php declare(strict_types=1);

namespace XBase\Header\Column\Validator\DBase;

use XBase\Enum\FieldType;
use XBase\Exception\ColumnException;
use XBase\Header\Column;
use XBase\Header\Column\Validator\ColumnValidatorInterface;

class NumberValidator implements ColumnValidatorInterface
{
    const MAX_LENGTH = 20;

    public function getType(): string
    {
        return FieldType::NUMERIC;
    }

    public function validate(Column $column): void
    {
        if (empty($column->length) || $column->length < 1 || $column->length > self::MAX_LENGTH) {
            throw new ColumnException(sprintf('Char column length must be in range [1, %s]', self::MAX_LENGTH));
        }

        if ($column->decimalCount > $column->length) {
            throw new ColumnException('Property decimalCount greater than length');
        }
    }
}
