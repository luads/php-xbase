<?php declare(strict_types=1);

namespace XBase\Record;

use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Header\Column;
use XBase\Memo\MemoObject;
use XBase\Table\Table;

abstract class AbstractRecord implements RecordInterface
{
    public const FLAG_NOT_DELETED = 0x20;
    public const FLAG_DELETED = 0x2a;

    /** @var Table */
    protected $table;
    /** @var array */
    protected $data;
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
    }

    public function destroy(): void
    {
        $this->table = null;
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function __set(string $columnName, $value)
    {
        $column = $this->table->getColumn($columnName);

        if ((FieldType::DATETIME == $column->type || FieldType::DATE == $column->type) && is_string($value)) {
            $value = strtotime($value);
        }

        $this->set($column->name, $value);
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
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
    public function get(string $columnName)
    {
        $column = $this->table->getColumn($columnName);

        switch ($column->type) {
            case FieldType::MEMO:
                return $this->getMemo($column);
            default:
                return $this->data[$column->name] ?? null;
        }
    }

    public function getGenuine(string $columnName)
    {
        return $this->data[$columnName] ?? null;
    }

    /**
     * Get DATE(D) or DATETIME(T) data as object of \DateTime class.
     */
    public function getDateTimeObject(string $columnName): ?\DateTimeInterface
    {
        $column = $this->table->getColumn($columnName);
        $this->checkType($column, FieldType::DATE);

        $data = $this->get($column->name);

        return \DateTime::createFromFormat('Ymd', $data);
    }

    public function getTimeStamp(string $columnName): int
    {
        return strtotime($this->get($columnName));
    }

    protected function getMemo(Column $column)
    {
        if (!TableType::hasMemo($this->table->getVersion())) {
            throw new \LogicException('Table not supports Memo');
        }

        if (null === $memoObject = $this->getMemoObject($column->name)) {
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

        return $this->table->memo->get($pointer);
    }

    //</editor-fold>

    //<editor-fold desc="setters">
    public function set(string $columnName, $value): RecordInterface
    {
        $column = $this->table->getColumn($columnName);
        switch ($column->type) {
            case FieldType::CHAR:
                return $this->setString($column, $value);
            case FieldType::DOUBLE:
            case FieldType::FLOAT:
                return $this->setFloat($column, $value);
            case FieldType::DATE:
            case FieldType::DATETIME:
                return $this->setDate($column, $value);
            case FieldType::LOGICAL:
                return $this->setBoolean($column, $value);
            case FieldType::MEMO:
                return $this->setMemo($column, $value);
            case FieldType::NUMERIC:
                return $this->setNum($column, $value);
            case FieldType::IGNORE:
                return $this;
            default:
                $this->setGenuine($column->name, $value);
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

    protected function setString(Column $column, $value): self
    {
        if (is_string($value) && mb_strlen($value) > $column->length) {
            @trigger_error('Value length greater than column length');
            $value = substr($value, 0, $column->length);
        }
        $this->setGenuine($column->name, $value);

        return $this;
    }

    protected function setNum(Column $column, $value): self
    {
        $this->checkType($column, FieldType::NUMERIC);

        if (is_string($value)) {
            $value = (float) str_replace(',', '.', $value);
        }

        $this->setGenuine($column->name, $value);

        return $this;
    }

    protected function setFloat(Column $column, $value): self
    {
        $this->checkType($column, [FieldType::DOUBLE, FieldType::FLOAT]);

        if (is_numeric($value)) {
            $this->data[$column->name] = (float) $value;
        } elseif (is_string($value)) {
            $value = str_replace(',', '.', trim($value));
        }

        if (null === $value || '' === $value) {
            $this->data[$column->name] = null;
        }

        return $this;
    }

    protected function setDate(Column $column, $value): self
    {
        $this->checkType($column, FieldType::DATE);

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Ymd');
        } elseif (is_int($value)) {
            $value = date('Ymd', $value);
        }

        $this->setGenuine($column->name, $value);

        return $this;
    }

    protected function setBoolean(Column $column, bool $value): self
    {
        $this->checkType($column, FieldType::LOGICAL);

        if (is_bool($value)) {
            $this->setGenuine($column->name, $value);
        } elseif (is_string($value)) {
            switch (strtoupper($value)) {
                case 'T':
                case 'Y':
                case 'J':
                case '1':
                    $this->data[$column->name] = true;
                    break;
                case 'F':
                case 'N':
                case '0':
                    $this->data[$column->name] = false;
                    break;
                default:
                    $this->data[$column->name] = null;
            }
        }

        return $this;
    }

    /**
     * @param $value
     */
    protected function setMemo(Column $column, $value): RecordInterface
    {
        if (empty($this->data[$column->name]) && $value) {
            $this->data[$column->name] = $this->table->memo->create($value); //todo
        } elseif (!empty($this->data[$column->name])) {
            $pointer = $this->data[$column->name];
            $this->table->memo->update($pointer, $value); //todo
        }

        return $this;
    }

    //</editor-fold>

    public function copyFrom(RecordInterface $record): void
    {
        foreach ($this->table->header->columns as $column) {
            $this->set($column->name, $record->get($column->name));
        }
    }

    /**
     * Returns typed column values according to their types.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param string|array $types
     */
    protected function checkType(Column $column, $types): void
    {
        if (!is_array($types)) {
            $types = [$types];
        }

        if (!in_array($column->type, $types)) {
            trigger_error(
                sprintf("Column '%s' is not one of types [%s]", $column->name, implode(', ', $types)),
                E_USER_ERROR
            );
        }
    }
}
