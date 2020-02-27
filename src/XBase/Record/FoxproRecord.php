<?php

namespace XBase\Record;

use XBase\Column\ColumnInterface;
use XBase\Enum\FieldType;

class FoxproRecord extends AbstractRecord
{
    public function getObject(ColumnInterface $column)
    {
        switch ($column->getType()) {
            case FieldType::FLOAT:
                return $this->getFloat($column->getName());
            case FieldType::GENERAL:
                return $this->getGeneral($column->getName());
            default:
                return parent::getObject($column);
        }
    }

    public function getGeneral(string $columnName)
    {
        return $this->table->getMemo()->get($this->choppedData[$columnName])->getData();
    }

    /**
     * @return int
     */
    public function getFloat(string $columnName)
    {
        return (float) ltrim($this->choppedData[$columnName]);
    }
}
