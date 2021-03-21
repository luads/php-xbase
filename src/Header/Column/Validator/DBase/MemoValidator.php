<?php declare(strict_types=1);

namespace XBase\Header\Column\Validator\DBase;

use XBase\Enum\FieldType;
use XBase\Header\Column;
use XBase\Header\Column\Validator\ColumnValidatorInterface;

class MemoValidator implements ColumnValidatorInterface
{
    const LENGTH = 10;

    public function getType(): array
    {
        return [
            FieldType::MEMO,
        ];
    }

    public function validate(Column $column): void
    {
        $column->length = self::LENGTH;
    }
}
