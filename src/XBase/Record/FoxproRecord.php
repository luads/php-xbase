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
        return $this->data[$columnName];
    }

    /**
     * @return int
     *
     * @deprecated use __get or getObject function
     */
    public function getFloat(string $columnName)
    {
        return $this->data[$columnName];
    }
}
