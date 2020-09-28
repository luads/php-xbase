<?php declare(strict_types=1);

namespace XBase\Record;

use XBase\Enum\FieldType;

class DBase4Record extends AbstractRecord
{
    public function get($columnName)
    {
        $column = $this->toColumn($columnName);

        switch ($column->getType()) {
            case FieldType::DBASE4_BLOB: //todo dbase7 or 5 or 4? need to find documentation
                return $this->getMemo($column->getName());
            default:
                return parent::get($columnName);
        }
    }
}
