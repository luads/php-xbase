<?php declare(strict_types=1);

namespace XBase\Record;

use XBase\Enum\FieldType;

class DBase4Record extends AbstractRecord
{
    public function get(string $columnName)
    {
        $column = $this->table->getColumn($columnName);

        switch ($column->type) {
            case FieldType::DBASE4_BLOB: //todo dbase7 or 5 or 4? need to find documentation
                return $this->getMemo($column);
            default:
                return parent::get($columnName);
        }
    }

    public function set(string $columnName, $value): RecordInterface
    {
        $column = $this->table->getColumn($columnName);

        switch ($column->type) {
            case FieldType::DBASE4_BLOB: //todo dbase7 or 5 or 4? need to find documentation
                return $this->setMemo($column, $value);
            default:
                return parent::set($columnName, $value);
        }
    }
}
