<?php declare(strict_types=1);

namespace XBase\DataConverter\Field;

use XBase\Header\Column;
use XBase\Table\Table;

abstract class AbstractFieldDataConverter implements FieldDataConverterInterface
{
    /** @var Table */
    protected $table;

    /** @var Column */
    protected $column;

    public function __construct(Table $table, Column $column)
    {
        $this->table = $table;
        $this->column = $column;
    }
}
