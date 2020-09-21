<?php declare(strict_types=1);

namespace XBase\DataConverter\Field;

use XBase\Column\ColumnInterface;
use XBase\Table;

abstract class AbstractFieldDataConverter implements FieldDataConverterInterface
{
    /** @var Table */
    protected $table;

    /** @var ColumnInterface */
    protected $column;

    public function __construct(Table $table, ColumnInterface $column)
    {
        $this->table = $table;
        $this->column = $column;
    }
}
