<?php

namespace XBase\Record;

use XBase\Column\ColumnInterface;
use XBase\Enum\FieldType;

class DBase4Record extends DBaseRecord
{
    public function getObject(ColumnInterface $column)
    {
        switch ($column->getType()) {
            case FieldType::FLOAT:
                return $this->getFloat($column->getName());
            default:
                return parent::getObject($column);
        }
    }

    /**
     * @param string $columnName
     *
     * @return int
     */
    public function getFloat($columnName)
    {
        return (float) ltrim($this->choppedData[$columnName]);
    }
}
