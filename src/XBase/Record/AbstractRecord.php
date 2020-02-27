<?php

namespace XBase\Record;

use XBase\Column\ColumnInterface;
use XBase\DBaseColumn;
use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Exception\InvalidColumnException;
use XBase\Memo\MemoObject;
use XBase\Table;

class AbstractRecord implements RecordInterface
{
    const FLAG_NOT_DELETED = 0x20;
    const FLAG_DELETED     = 0x2a;

    /** @var Table */
    protected $table;
    /** @var array */
    protected $choppedData;
    /** @var bool */
    protected $deleted;
    /** @var bool */
    protected $inserted;
    /** @var int */
    protected $recordIndex;

    /**
     * Record constructor.
     *
     * @param Table $table
     * @param       $recordIndex
     * @param bool  $rawData
     */
    public function __construct(Table $table, $recordIndex, $rawData = false)
    {
        $this->table = $table;
        $this->recordIndex = $recordIndex;
        $this->choppedData = [];

        if ($rawData && strlen($rawData) > 0) {
            $this->inserted = false;
            $this->deleted = (self::FLAG_NOT_DELETED !== ord($rawData[0]));

            foreach ($table->getColumns() as $column) {
                $this->choppedData[$column->getName()] = substr($rawData, $column->getBytePos(), $column->getDataLength());
            }
        } else {
            $this->inserted = true;
            $this->deleted = false;

            foreach ($table->getColumns() as $column) {
                $this->choppedData[$column->getName()] = str_pad('', $column->getDataLength(), chr(0));
            }
        }
    }

    public function destroy()
    {
        $this->table = null;
        $this->choppedData = null;
    }

    public function __get($name)
    {
        return $this->getString($name);
    }

    public function __set($name, $value)
    {
        return $this->setStringByName($name, $value);
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @return bool
     */
    public function isInserted()
    {
        return $this->inserted;
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
    public function getColumn($name)
    {
        return $this->table->getColumn($name);
    }

    /**
     * @return int
     */
    public function getRecordIndex()
    {
        return $this->recordIndex;
    }

    public function setRecordIndex($index)
    {
        $this->recordIndex = $index;
    }

    public function getString(string $columnName)
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

            return $result;
        }
    }

    /**
     * @param string $columnName
     *
     * @return false|string|null
     */
    public function forceGetString($columnName)
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
     * @param ColumnInterface $column
     *
     * @return bool|false|float|int|string|null
     *
     * @throws InvalidColumnException If dataType not exists
     */
    public function getObject(ColumnInterface $column)
    {
        switch ($column->getType()) {
            case FieldType::CHAR:
                return $this->getString($column->getName());
            case FieldType::DATE:
                return $this->getDate($column->getName());
            case FieldType::LOGICAL:
                return $this->getBoolean($column->getName());
            case FieldType::MEMO:
                return $this->getMemo($column->getName());
            case FieldType::NUMERIC:
                return $this->getNum($column->getName());
            case FieldType::IGNORE:
                return false;
            case FieldType::BLOB:
                return $this->forceGetString($column->getName());
        }

        throw new InvalidColumnException(sprintf('Cannot handle datatype %s', $column->getType()));
    }

    /**
     * @param string $columnName
     *
     * @return false|string|null
     */
    public function getChar($columnName)
    {
        return $this->forceGetString($columnName);
    }

    /**
     * @param string $columnName
     *
     * @return bool|float|int
     */
    public function getNum($columnName)
    {
        $s = $this->forceGetString($columnName);

        if (!is_string($s)) {
            return false;
        }

        $s = str_replace(',', '.', $s);

        $column = $this->getColumn($columnName);

        if (FieldType::NUMERIC == $column->getType() && ($column->getDecimalCount() > 0 || $column->getLength() > 9)) {
            return doubleval($s);
        } else {
            return intval($s);
        }
    }

    /**
     * @param ColumnInterface $columnObj
     * @param                 $value
     *
     * @return bool
     */
    public function setNum($columnObj, $value)
    {
        if (FieldType::NUMERIC != $columnObj->getType()) {
            trigger_error($columnObj->getName().' is not a Number column', E_USER_ERROR);
        }

        if (0 == strlen($value)) {
            $this->forceSetString($columnObj, '');
            return false;
        }

        $value = str_replace(',', '.', $value);
        $this->forceSetString($columnObj, number_format($value, $columnObj->getDecimalCount(), '.', ''));
    }

    /**
     * @param string $columnName
     *
     * @return bool|false|int
     */
    public function getDate($columnName)
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
     * @param string $columnName
     *
     * @return bool
     */
    public function getBoolean($columnName)
    {
        $s = $this->forceGetString($columnName);

        if (!$s) {
            return false;
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
     * @param string $columnName
     *
     * @return false|string|null
     */
    public function getMemo($columnName)
    {
        if (!TableType::hasMemo($this->table->getVersion())) {
            throw new \LogicException('Table not supports Memo');
        }

        return $this->table->getMemo()->get($this->choppedData[$columnName])->getData();
    }

    public function getMemoObject(string $columnName): MemoObject
    {
        if (!TableType::hasMemo($this->table->getVersion())) {
            throw new \LogicException('Table not supports Memo');
        }

        return $this->table->getMemo()->get($this->choppedData[$columnName]);
    }

    /**
     * @param $record
     */
    public function copyFrom($record)
    {
        $this->choppedData = $record->choppedData;
    }

    /**
     * @param bool $bool
     */
    public function setDeleted($bool)
    {
        $this->deleted = $bool;
    }

    /**
     * @param $columnName
     * @param $value
     */
    public function setStringByName($columnName, $value)
    {
        $this->setString($this->table->getColumn($columnName), $value);
    }

    /**
     * @param $columnIndex
     * @param $value
     */
    public function setStringByIndex($columnIndex, $value)
    {
        $this->setString($this->table->getColumn($columnIndex), $value);
    }

    /**
     * @param ColumnInterface $columnObj
     * @param                 $value
     */
    public function setString($columnObj, $value)
    {
        if (FieldType::CHAR == $columnObj->getType()) {
            $this->forceSetString($columnObj, $value);
        } else {
            if ((FieldType::DATETIME == $columnObj->getType() || FieldType::DATE == $columnObj->getType()) && is_string($value)) {
                $value = strtotime($value);
            }

            $this->setObject($columnObj, $value);
        }
    }

    /**
     * @param ColumnInterface $columnObj
     * @param                 $value
     */
    public function forceSetString($columnObj, $value)
    {
        if ($this->table->getConvertFrom()) {
            $value = iconv('utf-8', $this->table->getConvertFrom(), $value);
        }

        $this->choppedData[$columnObj->getName()] = str_pad(substr($value, 0, $columnObj->getDataLength()), $columnObj->getDataLength(), ' ');
    }

    /**
     * @param string $columnName
     * @param        $value
     *
     * @return bool
     */
    public function setObjectByName($columnName, $value)
    {
        return $this->setObject($this->table->getColumn($columnName), $value);
    }

    /**
     * @param int $columnIndex
     * @param     $value
     *
     * @return bool
     */
    public function setObjectByIndex($columnIndex, $value)
    {
        return $this->setObject($this->table->getColumn($columnIndex), $value);
    }

    /**
     * @param ColumnInterface $columnObj
     * @param                 $value
     *
     * @return bool
     */
    public function setObject($columnObj, $value)
    {
        switch ($columnObj->getType()) {
            case FieldType::CHAR:
                $this->setString($columnObj, $value);
                return false;
            case FieldType::DOUBLE:
            case FieldType::FLOAT:
                $this->setFloat($columnObj, $value);
                return false;
            case FieldType::DATE:
                $this->setDate($columnObj, $value);
                return false;
            case FieldType::DATETIME:
                $this->setDateTime($columnObj, $value);
                return false;
            case FieldType::LOGICAL:
                $this->setBoolean($columnObj, $value);
                return false;
            case FieldType::MEMO:
                $this->setMemo($columnObj, $value);
                return false;
            case FieldType::NUMERIC:
                $this->setNum($columnObj, $value);
                return false;
            case FieldType::IGNORE:
                return false;
        }

        trigger_error('cannot handle datatype'.$columnObj->getType(), E_USER_ERROR);
    }

    /**
     * @param ColumnInterface $columnObj
     * @param                 $value
     *
     * @return bool
     */
    public function setDate($columnObj, $value)
    {
        if (FieldType::DATE != $columnObj->getType()) {
            trigger_error($columnObj->getName().' is not a Date column', E_USER_ERROR);
        }

        if ($value instanceof \DateTimeInterface) {
            $this->forceSetString($columnObj, $value->format('Ymd'));
            return false;
        }

        if (0 == strlen($value)) {
            $this->forceSetString($columnObj, '');
            return false;
        }

        $this->forceSetString($columnObj, date('Ymd', $value));
    }

    /**
     * @param ColumnInterface $columnObj
     * @param                 $value
     *
     * @return bool
     */
    public function setBoolean($columnObj, $value)
    {
        if (FieldType::LOGICAL != $columnObj->getType()) {
            trigger_error($columnObj->getName().' is not a DateTime column', E_USER_ERROR);
        }

        switch (strtoupper($value)) {
            case 'T':
            case 'Y':
            case 'J':
            case '1':
            case 'F':
            case 'N':
            case '0':
                $this->forceSetString($columnObj, $value);
                return false;
            case true:
                $this->forceSetString($columnObj, 'T');
                return false;
            default:
                $this->forceSetString($columnObj, 'F');
        }
    }

    /**
     * @param ColumnInterface $columnObj
     * @param                 $value
     */
    public function setMemo($columnObj, $value)
    {
        if (FieldType::MEMO != $columnObj->getType()) {
            trigger_error($columnObj->getName().' is not a Memo column', E_USER_ERROR);
        }

        $this->forceSetString($columnObj, $value);
    }

    /**
     * @return string
     */
    public function serializeRawData()
    {
        return chr($this->deleted ? self::FLAG_DELETED : self::FLAG_NOT_DELETED).implode('', $this->choppedData);
    }

    /**
     * Returns typed column values according to their types
     *
     * @return array
     */
    public function getData()
    {
        $fields = [];

        foreach ($this->getColumns() as $column) {
            $fields[$column->getName()] = $this->getObject($column);
        }

        return $fields;
    }

    /**
     * Returns raw values trimmed and converted according to encoding
     *
     * @return array|string[]
     */
    public function getChoppedData()
    {
        $fields = [];

        foreach ($this->choppedData as $columnName => $columnValue) {
            $fields[$columnName] = $this->forceGetString($columnName);
        }

        return $fields;
    }
}
