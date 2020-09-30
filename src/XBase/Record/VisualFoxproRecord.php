<?php declare(strict_types=1);

namespace XBase\Record;

use XBase\Enum\FieldType;

/**
 * Visual Foxpro record.
 */
class VisualFoxproRecord extends FoxproRecord
{
    public function get($columnName)
    {
        $column = $this->toColumn($columnName);

        switch ($column->getType()) {
            case FieldType::BLOB:
                return $this->getMemo($column->getName());
            case FieldType::GENERAL:
                return $this->data[$column->getName()];
            default:
                return parent::get($columnName);
        }
    }

    public function set($columnName, $value): RecordInterface
    {
        $column = $this->toColumn($columnName);
        switch ($column->getType()) {
            case FieldType::BLOB:
            case FieldType::MEMO:
                return $this->setMemo($column->getName(), $value);
            case FieldType::INTEGER:
                return $this->setInt($column->getName(), $value);
            case FieldType::DOUBLE:
                return $this->setDouble($column->getName(), $value);
            case FieldType::DATETIME:
                return $this->setDateTime($column->getName(), $value);
            case FieldType::CURRENCY:
                return $this->setCurrency($column->getName(), $value);
            case FieldType::GENERAL:
                return $this->setGeneral($column->getName(), $value);
            case FieldType::FLOAT:
                return $this->setFloat($column->getName(), $value);
            case FieldType::VAR_FIELD:
            case FieldType::VARBINARY:
                return $this->setVarchar($column->getName(), $value);
            default:
                return parent::set($column->getName(), $value);
        }
    }

    /**
     * @param $value
     */
    public function setMemo($columnName, $value): RecordInterface
    {
        $column = $this->toColumn($columnName);
        $this->checkType($column, [FieldType::BLOB, FieldType::MEMO]);

        if (empty($this->data[$column->getName()]) && $value) {
            $this->data[$column->getName()] = $this->table->getMemo()->create($value);
        } elseif (!empty($this->data[$column->getName()])) {
            $this->data[$column->getName()] = $this->table->getMemo()->update($this->data[$column->getName()], $value);
        }

        return $this;
    }

    public function setGeneral($columnName, $value): self
    {
        $column = $this->toColumn($columnName);
        $this->checkType($column, FieldType::GENERAL);

        if (null !== $value) {
            $value = (int) $value;
        }

        $this->data[$column->getName()] = $value;

        return $this;
    }

    public function setInt($columnName, $value): self
    {
        $column = $this->toColumn($columnName);
        $this->checkType($column, FieldType::INTEGER);

        if (null !== $value) {
            $value = (int) $value;
        }

        $this->data[$column->getName()] = $value;

        return $this;
    }

    public function setDouble($columnName, $value): self
    {
        $column = $this->toColumn($columnName);
        $this->checkType($column, FieldType::DOUBLE);

        if (is_string($value)) {
            $value = (float) str_replace(',', '.', trim($value));
        }

        $this->data[$column->getName()] = $value;

        return $this;
    }

    private function setCurrency($columnName, $value): self
    {
        $column = $this->toColumn($columnName);
        $this->checkType($column, FieldType::CURRENCY);

        if (is_string($value)) {
            $value = (float) str_replace(',', '.', trim($value));
        }

        $this->data[$column->getName()] = $value;

        return $this;
    }

    private function setVarchar($columnName, $value): self
    {
        $column = $this->toColumn($columnName);
        $this->checkType($column, [FieldType::VAR_FIELD, FieldType::VARBINARY]);

        $this->data[$column->getName()] = $value;

        return $this;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function setDateTime($columnName, $value): self
    {
        $column = $this->toColumn($columnName);
        $this->checkType($column, FieldType::DATETIME);

        if (is_int($value)) {
            $value = \DateTime::createFromFormat('U', $value);
        } elseif (is_string($value)) {
            $value = new \DateTime($value);
        }

        $this->data[$column->getName()] = $value;

        return $this;
    }

    /**
     * Get DATE(D) or DATETIME(T) data as object of \DateTime class.
     */
    public function getDateTimeObject($columnName): ?\DateTimeInterface
    {
        $column = $this->getColumn($columnName);
        $this->checkType($column, [FieldType::DATE, FieldType::DATETIME]);
        if (in_array($column->getType(), [FieldType::DATETIME])) {
            return $this->getDateTime($column->getName());
        }

        return parent::getDateTimeObject($column->getName());
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use get()
     */
    public function getGeneral(string $columnName)
    {
        return $this->get($columnName);
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use get()
     */
    public function getDateTime(string $columnName)
    {
        return $this->get($columnName);
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use get()
     */
    public function getVarbinary(string $columnName)
    {
        return $this->get($columnName);
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use get()
     */
    public function getVarchar(string $columnName)
    {
        return $this->get($columnName);
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use get()
     */
    public function getCurrency(string $columnName)
    {
        return $this->get($columnName);
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use get()
     */
    public function getDouble(string $columnName)
    {
        return $this->data[$columnName];
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use get()
     */
    public function getInt(string $columnName)
    {
        return $this->data[$columnName];
    }
}
