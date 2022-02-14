<?php declare(strict_types=1);

namespace XBase\DataConverter\Field;

use XBase\DataConverter\Encoder\EncoderInterface;
use XBase\Header\Column;
use XBase\Table\Table;

abstract class AbstractFieldDataConverter implements FieldDataConverterInterface
{
    /** @var Table */
    protected $table;

    /** @var Column */
    protected $column;

    /** @var EncoderInterface */
    protected $encoder;

    public function __construct(Table $table, Column $column, EncoderInterface $encoder)
    {
        $this->table = $table;
        $this->column = $column;
        $this->encoder = $encoder;
    }
}
