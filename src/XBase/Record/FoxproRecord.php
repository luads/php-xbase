<?php declare(strict_types=1);

namespace XBase\Record;

use XBase\Enum\FieldType;

class FoxproRecord extends AbstractRecord
{
    public function get($columnName)
    {
        $column = $this->toColumn($columnName);

        switch ($column->getType()) {
            case FieldType::GENERAL:
                return $this->getGeneral($column->getName());
            default:
                return parent::get($columnName);
        }
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use get()
     */
    public function getGeneral(string $columnName)
    {
        $column = $this->toColumn($columnName);
        $this->checkType($column, FieldType::GENERAL);

        return $this->getMemoObject($columnName)->getData();
    }
}
