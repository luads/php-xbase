<?php declare(strict_types=1);

namespace XBase\Record;

use XBase\Column\ColumnInterface;
use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Memo\MemoObject;
use XBase\Table;

abstract class AbstractRecord implements RecordInterface
{
    public const FLAG_NOT_DELETED = 0x20;
    public const FLAG_DELETED = 0x2a;

    /** @var Table */
    protected $table;
    /** @var array */
    protected $data;
    /**
     * @var array
     *
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

    public function setDeleted(bool $bool): self
    {
        $this->deleted = $bool;

        return $this;
    }

    //<editor-fold desc="getters">
    public function get($columnName)
    {
        $column = $this->toColumn($columnName);

        switch ($column->getType()) {
            case FieldType::MEMO:
                return $this->getMemo($column->getName());
            default:
                return $this->data[$column->getName()] ?? null;
        }
    }

    public function getGenuine(string $columnName)
    {
        return $this->data[$columnName] ?? null;
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use get()
     */
    public function getObject(ColumnInterface $column)
    {
        return $this->get($column->getName());
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use get()
     */
    public function getChar(string $columnName)
    {
        return $this->get($columnName);
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use get()
     */
    public function getString(string $columnName): string
    {
        return (string) $this->get($columnName);
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use get()
     */
    public function getNum(string $columnName)
    {
        return $this->get($columnName);
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use set()
     */
    public function getDate(string $columnName)
    {
        return $this->get($columnName);
    }

    /**
     * Get DATE(D) or DATETIME(T) data as object of \DateTime class.
     */
    public function getDateTimeObject($columnName): ?\DateTimeInterface
    {
        $column = $this->getColumn($columnName);
        $this->checkType($column, FieldType::DATE);

        $data = $this->get($column->getName());

        return \DateTime::createFromFormat('Ymd', $data);
    }

    public function getTimeStamp($columnName): int
    {
        return strtotime($this->getDate($columnName));
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use set()
     */
    public function getBoolean(string $columnName)
    {
        return $this->get($columnName);
    }

    /**
     * @return false|string|null
     */
    public function getMemo(string $columnName)
    {
        if (!TableType::hasMemo($this->table->getVersion())) {
            throw new \LogicException('Table not supports Memo');
        }

        if (null === $memoObject = $this->getMemoObject($columnName)) {
            return null;
        }

        return $memoObject->getData();
    }

    public function getMemoObject(string $columnName): ?MemoObject
    {
        if (!TableType::hasMemo($this->table->getVersion())) {
            throw new \LogicException('Table not supports Memo');
        }

        if (!$pointer = $this->data[$columnName]) {
            return null;
        }

        return $this->table->getMemo()->get($pointer);
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use get()
     */
    public function getFloat(string $columnName)
    {
        return $this->data[$columnName];
    }

    //</editor-fold>

    //<editor-fold desc="setters">
    public function set($columnName, $value): RecordInterface
    {
        $column = $this->toColumn($columnName);
        switch ($column->getType()) {
            case FieldType::CHAR:
                return $this->setString($column->getName(), $value);
            case FieldType::DOUBLE:
            case FieldType::FLOAT:
                return $this->setFloat($column->getName(), $value);
            case FieldType::DATE:
                return $this->setDate($column->getName(), $value);
            case FieldType::DATETIME:
//                $this->setDateTime($column, $value);
            case FieldType::LOGICAL:
                return $this->setBoolean($column->getName(), $value);
            case FieldType::MEMO:
                return $this->setMemo($column->getName(), $value);
            case FieldType::NUMERIC:
                return $this->setNum($column->getName(), $value);
            case FieldType::IGNORE:
                return $this;
            default:
                $this->setGenuine($column->getName(), $value);
        }

        return $this;
    }

    public function setGenuine(string $columnName, $value): self
    {
        $this->data[$columnName] = $value;

        return $this;
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use set()
     */
    public function setObject($column, $value)
    {
        return $this->set($column, $value);
    }

    /**
     * @param string|ColumnInterface $columnName
     */
    public function setString($columnName, $value): self
    {
        $column = $this->toColumn($columnName);
        $this->setGenuine($column->getName(), $value);

        return $this;
    }

    /**
     * @param string|ColumnInterface $columnName
     */
    public function setNum($columnName, $value): self
    {
        $column = $this->toColumn($columnName);
        $this->checkType($column, FieldType::NUMERIC);

        if (is_string($value)) {
            $value = (float) str_replace(',', '.', $value);
        }

        $this->setGenuine($column->getName(), $value);

        return $this;
    }

    public function setFloat($columnName, $value): self
    {
        $column = $this->toColumn($columnName);
        $this->checkType($column, [FieldType::DOUBLE, FieldType::FLOAT]);

        if (is_numeric($value)) {
            $this->data[$column->getName()] = (float) $value;
        } elseif (is_string($value)) {
            $value = str_replace(',', '.', trim($value));
        }

        if (null === $value || '' === $value) {
            $this->data[$column->getName()] = null;
        }

        return $this;
    }

    /**
     * @param string|ColumnInterface    $columnName
     * @param string|\DateTimeInterface $value
     *
     * @return bool
     */
    public function setDate($columnName, $value): self
    {
        $column = $this->toColumn($columnName);
        $this->checkType($column, FieldType::DATE);

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Ymd');
        } elseif (is_int($value)) {
            $value = date('Ymd', $value);
        }

        $this->setGenuine($column->getName(), $value);

        return $this;
    }

    /**
     * @param ColumnInterface|string $columnName
     */
    public function setBoolean($columnName, bool $value): self
    {
        $column = $this->toColumn($columnName);
        $this->checkType($column, FieldType::LOGICAL);

        if (is_bool($value)) {
            $this->setGenuine($column->getName(), $value);
        } elseif (is_string($value)) {
            switch (strtoupper($value)) {
                case 'T':
                case 'Y':
                case 'J':
                case '1':
                    $this->data[$column->getName()] = true;
                    break;
                case 'F':
                case 'N':
                case '0':
                    $this->data[$column->getName()] = false;
                    break;
                default:
                    $this->data[$column->getName()] = null;
            }
        }

        return $this;
    }

    /**
     * @param $value
     */
    public function setMemo($columnName, $value): RecordInterface
    {
        $column = $this->toColumn($columnName);
        $this->checkType($column, FieldType::MEMO);

        if (empty($this->data[$column->getName()]) && $value) {
            $this->data[$column->getName()] = $this->table->getMemo()->create($value); //todo
        } elseif (!empty($this->data[$column->getName()])) {
            $pointer = $this->data[$column->getName()];
            $this->table->getMemo()->update($pointer, $value); //todo
        }

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
     * @deprecated since 1.3 and will be delete in 2.0. Use set()
     */
    public function setStringByName(string $columnName, $value): void
    {
        $this->setString($this->table->getColumn($columnName), $value);
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use set()
     */
    public function setStringByIndex($columnIndex, $value): void
    {
        $this->setString($this->table->getColumn($columnIndex), $value);
    }

    /**
     * @deprecated since 1.3 and will be deleted in 2.0. Use get()
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
     * @deprecated since 1.3 and will be deleted in 2.0. Use set()
     */
    public function forceSetString(ColumnInterface $column, $value): void
    {
        if ($this->table->getConvertFrom()) {
            $value = iconv('utf-8', $this->table->getConvertFrom(), $value);
        }

        $this->choppedData[$column->getName()] = substr($value, 0, $column->getLength());
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use set()
     */
    public function setObjectByName(string $columnName, $value)
    {
        return $this->setObject($this->table->getColumn($columnName), $value);
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use set()
     */
    public function setObjectByIndex($columnIndex, $value)
    {
        return $this->setObject($this->table->getColumn($columnIndex), $value);
    }

    /**
     * Returns typed column values according to their types.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns raw values trimmed and converted according to encoding.
     *
     * @return array|string[]
     *
     * @deprecated since 1.3 and will be deleted in 2.0. Use getData()
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
        if (is_string($columnName)) {
            return $this->getColumn($columnName);
        }

        if ($columnName instanceof ColumnInterface) {
            return $columnName;
        }

        throw new \LogicException('Incorrect first argument');
    }

    /**
     * @param string|array $types
     */
    protected function checkType(ColumnInterface $column, $types): void
    {
        if (!is_array($types)) {
            $types = [$types];
        }

        if (!in_array($column->getType(), $types)) {
            trigger_error(
                sprintf("Column '%s' is not one of types [%s]", $column->getName(), implode(', ', $types)),
                E_USER_ERROR
            );
        }
    }
}
