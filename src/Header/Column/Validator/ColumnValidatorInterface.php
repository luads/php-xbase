<?php declare(strict_types=1);

namespace XBase\Header\Column\Validator;

use XBase\Header\Column;

interface ColumnValidatorInterface
{
    /**
     * @return string|array
     */
    public function getType();

    public function validate(Column $column): void;
}
