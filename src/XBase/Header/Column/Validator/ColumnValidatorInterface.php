<?php declare(strict_types=1);

namespace XBase\Header\Column\Validator;

use XBase\Header\Column;

interface ColumnValidatorInterface
{
    public function getType(): string;

    public function validate(Column $column): void;
}
