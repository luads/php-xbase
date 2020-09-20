<?php

namespace XBase\Record;

use XBase\Column\ColumnInterface;
use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Exception\InvalidColumnException;
use XBase\Memo\MemoObject;
use XBase\Table;

class AbstractRecord implements RecordInterface
{
    public const FLAG_NOT_DELETED = 0x20;
    public const FLAG_DELETED     = 0x2a;

    /** @var Table */
    protected $table;
    /** @var array */
    protected $data;
    /**
     * @var array
     * @deprecated
     */
    protected $choppedData = [];
    /** @var bool */
    protected $deleted = false;
    /** @var int */
    protected $recordIndex;

    public function __construct(Table $table, int $recordIndex, array $data = [])
    {
        $this->table = $table;
        $this->recordIndex = $recordIndex;
        $this->data = $data['data'] ?? [];
        $this->deleted = $data['deleted'] ?? false;
        $this->choppedData = $data['choppedData'] ?? [];
    }

    public function destroy(): void
    {
        $this->table = null;
        $this->choppedData = null;
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function __set(string $columnName, $value)
    {
        $column = $this->toColumn($columnName);

        if ((FieldType::DATETIME == $column->getType() || FieldType::DATE == $column->getType()) && is_string($value)) {
            $value = strtotime($value);
        }

        $this->setObject($column->getName(), $value);
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @return ColumnInterface[]
     */
    public function getColumns()
    {
        return $this->table->getColumns();
    }

    /**
     * @param $name
     *
     * @return ColumnInterface
     */
    public function getColumn(string $name)
    {
        return $this->table->getColumn($name);
    }

    public function getRecordIndex(): int
    {
        return $this->recordIndex;
    }

    public function setRecordIndex(int $index): void
    {
        $this->recordIndex = $index;
    }

    /**
     * @param bool $bool
     */
    public function setDeleted($bool)
    {
        $this->deleted = $bool;
    }

    //<editor-fold desc="getters">
    public function get(string $columnName)
    {
        if (!$column = $this->table->getColumn($columnName)) {
            throw new \LogicException("Column $columnName not found");
        }

        return $this->data[$columnName] ?? null;
    }

    /**
     * @return bool|false|float|int|string|null
     *
     * @throws InvalidColumnException If dataType not exists
     */
    public function getObject(ColumnInterface $column)
    {
        return $this->data[$column->getName()];
//        switch ($column->getType()) {
//            case FieldType::CHAR:
//                return $this->getString($column->getName());
//            case FieldType::DATE:
//                return $this->getDate($column->getName());
//            case FieldType::LOGICAL:
//                return $this->getBoolean($column->getName());
//            case FieldType::MEMO:
//                return $this->getMemo($column->getName());
//            case FieldType::NUMERIC:
//                return $this->getNum($column->getName());
//            case FieldType::IGNORE:
//                return false;
//            case FieldType::BLOB:
//                return $this->forceGetString($column->getName());
//        }
//
//        throw new InvalidColumnException(sprintf('Cannot handle datatype %s', $column->getType()));
    }

    /**
     * @deprecated use getString
     */
    public function getChar(string $columnName)
    {
        return $this->forceGetString($columnName);
    }

    public function getString(string $columnName): string
    {
        $column = $this->table->getColumn($columnName);

        if (FieldType::CHAR == $column->getType()) {
            return $this->forceGetString($columnName);
        } else {
            $result = $this->getObject($column);

            if ($result && (FieldType::DATETIME == $column->getType() || FieldType::DATE == $column->getType())) {
                return date('r', $result);
            }

            if (FieldType::LOGICAL == $column->getType()) {
                return $result ? '1' : '0';
            }

            return (string) $result;
        }
    }

    /**
     * @return bool|float|int
     */
    public function getNum(string $columnName)
    {
        if (is_string($value = $this->data[$columnName])) {
            $value = str_replace(',', '.', $value);
            $column = $this->getColumn($columnName);

            if (FieldType::NUMERIC == $column->getType() && ($column->getDecimalCount() > 0 || $column->getLength() > 9)) {
                $value = doubleval($value);
            } else {
                $value = intval($value);
            }
        }

        return $value;
    }

    /**
     * @return bool|int
     */
    public function getDate(string $columnName)
    {
        $s = $this->forceGetString($columnName);

        if (!$s) {
            return false;
        }

        return strtotime($s);
    }

    /**
     * Get DATE(D) or DATETIME(T) data as object of \DateTime class
     */
    public function getDateTimeObject(string $columnName): \DateTime
    {
        $column = $this->getColumn($columnName);
        if (!in_array($column->getType(), [FieldType::DATE])) {
            trigger_error($column->getName().' is not a Date or DateTime column', E_USER_ERROR);
        }

        $data = $this->forceGetString($columnName);

        return new \DateTime($data);
    }

    /**
     * @return bool
     */
    public function getBoolean(string $columnName)
    {
        $s = $this->forceGetString($columnName);

        if (!$s) {
            return false; //todo if null?
        }

        switch (strtoupper($s[0])) {
            case 'T':
            case 'Y':
            case 'J':
            case '1':
                return true;

            default:
                return false;
        }
    }

    /**
     * @return false|string|null
     */
    public function getMemo(string $columnName)
    {
        if (!TableType::hasMemo($this->table->getVersion())) {
            throw new \LogicException('Table not supports Memo');
        }

        return $this->data[$columnName]->getData();
    }

    public function getMemoObject(string $columnName): MemoObject
    {
        if (!TableType::hasMemo($this->table->getVersion())) {
            throw new \LogicException('Table not supports Memo');
        }

        return $this->data[$columnName];
    }
    //</editor-fold>

    //<editor-fold desc="setters">
    public function set(string $columnName, $value): RecordInterface
    {
        if (!$column = $this->table->getColumn($columnName)) {
            throw new \LogicException("Column $columnName not found");
        }

        $this->setObject($columnName, $value);

        return $this;
    }

    /**
     * @param string|ColumnInterface $columnName
     */
    public function setString($columnName, $value): self
    {
        $column = $this->toColumn($columnName);
        $this->data[$column->getName()] = $value;
        $this->forceSetString($column, $value);//todo remove 1.4

        return $this;
    }

    /**
     * @param string|ColumnInterface $columnName
     */
    public function setNum($columnName, $value): self
    {
        $column = $this->toColumn($columnName);
        if (FieldType::NUMERIC !== $column->getType()) {
            trigger_error($column->getName().' is not a Number column', E_USER_ERROR);
        }

        if (is_string($value)) {
            $value = (float) str_replace(',', '.', $value);
        }

        $this->data[$column->getName()] = $value;

        $this->forceSetString($column, number_format($value, $column->getDecimalCount(), '.', ''));//todo remove 1.4

        return $this;
    }

    public function setObject($column, $value)
    {
        $column = $this->toColumn($column);
        switch ($column->getType()) {
            case FieldType::CHAR:
                return $this->setString($column->getName(), $value);
//            case FieldType::DOUBLE:
//            case FieldType::FLOAT:
//                $this->setFloat($column, $value);
//                return false;
            case FieldType::DATE:
                return $this->setDate($column->getName(), $value);
//            case FieldType::DATETIME:
//                $this->setDateTime($column, $value);
            case FieldType::LOGICAL:
                return $this->setBoolean($column->getName(), $value);
            case FieldType::MEMO:
                return $this->setMemo($column->getName(), $value);
            case FieldType::NUMERIC:
                return $this->setNum($column->getName(), $value);
            case FieldType::IGNORE:
                return $this;
        }

        trigger_error('cannot handle datatype '.$column->getType(), E_USER_ERROR);
    }

    /**
     * @param string|ColumnInterface    $columnName
     * @param string|\DateTimeInterface $value
     *
     * @return bool
     */
    public function setDate($columnName, $value)
    {
        $column = $this->toColumn($columnName);
        if (FieldType::DATE !== $column->getType()) {
            trigger_error($column->getName().' is not a Date column', E_USER_ERROR);
        }

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Ymd');
        } elseif (is_int($value)) {
            $value = date('Ymd', $value);
        }

        $this->data[$column->getName()] = $value;
        $this->forceSetString($column, $value);

        return true;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function setBoolean($columnName, $value)
    {
        $column = $this->toColumn($columnName);
        if (FieldType::LOGICAL !== $column->getType()) {
            trigger_error($column->getName().' is not a DateTime column', E_USER_ERROR);
        }

        switch (strtoupper($value)) {
            case 'T':
            case 'Y':
            case 'J':
            case '1':
            case 'F':
            case 'N':
            case '0':
                $this->forceSetString($column, $value);
                return false;
            case true:
                $this->forceSetString($column, 'T');
                return false;
            default:
                $this->forceSetString($column, 'F');
        }
    }

    /**
     * @param $value
     */
    public function setMemo($columnName, $value): self
    {
        $column = $this->toColumn($columnName);
        if (FieldType::MEMO !== $column->getType()) {
            trigger_error($column->getName().' is not a Memo column', E_USER_ERROR);
        }

        if (empty($this->data[$columnName])) {
            $this->data[$columnName] = new MemoObject($value);
        }
        $this->data[$columnName]->setData($value);

        return $this;
    }

    //</editor-fold>

    public function copyFrom(RecordInterface $record): void
    {
        foreach ($this->table->getColumns() as $column) {
            $this->set($column->getName(), $record->get($column->getName()));
        }
    }

    /**
     * @deprecated
     */
    public function setStringByName(string $columnName, $value): void
    {
        $this->setString($this->table->getColumn($columnName), $value);
    }

    /**
     * @deprecated
     */
    public function setStringByIndex($columnIndex, $value): void
    {
        $this->setString($this->table->getColumn($columnIndex), $value);
    }

    /**
     * @deprecated since 1.3 will be deleted in 1.4. Use get()
     */
    public function forceGetString(string $columnName)
    {
        $data = trim($this->choppedData[$columnName]);

        if ($this->table->getConvertFrom()) {
            $data = iconv($this->table->getConvertFrom(), 'utf-8', $data);
        }

        if (!isset($data[0]) || 0 === ord($data[0])) {
            return null;
        }

        return $data;
    }

    /**
     * @deprecated since 1.3 will be deleted in 1.4. Use set()
     */
    public function forceSetString(ColumnInterface $column, $value): void
    {
        if ($this->table->getConvertFrom()) {
            $value = iconv('utf-8', $this->table->getConvertFrom(), $value);
        }

        $this->choppedData[$column->getName()] = substr($value, 0, $column->getLength());
    }

    /**
     * @deprecated
     */
    public function setObjectByName(string $columnName, $value)
    {
        return $this->setObject($this->table->getColumn($columnName), $value);
    }

    /**
     * @deprecated
     */
    public function setObjectByIndex($columnIndex, $value)
    {
        return $this->setObject($this->table->getColumn($columnIndex), $value);
    }

    /**
     * Returns typed column values according to their types
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns raw values trimmed and converted according to encoding
     *
     * @return array|string[]
     *
     * @deprecated since 1.3. Use getData()
     */
    public function getChoppedData()
    {
        $fields = [];

        foreach ($this->choppedData as $columnName => $columnValue) {
            $fields[$columnName] = $this->forceGetString($columnName);
        }

        return $fields;
    }

    protected function toColumn($columnName): ColumnInterface
    {
        if ($columnName instanceof ColumnInterface) {
            @trigger_error('You should pass string column name as first argument', E_USER_WARNING);
            return $columnName;
        }

        if (is_string($columnName)) {
            return $this->getColumn($columnName);
        }

        throw new \LogicException('Incorrect first argument');
    }
}
