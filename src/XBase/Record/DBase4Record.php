<?php declare(strict_types=1);

namespace XBase\Record;

class DBase4Record extends AbstractRecord
{
    public function get($columnName)
    {
        $column = $this->toColumn($columnName);

        switch ($column->getType()) {
            case 'B':
                return $this->getMemo($column->getName());
            default:
                return parent::get($columnName);
        }
    }
}
