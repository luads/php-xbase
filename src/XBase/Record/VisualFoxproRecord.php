<?php declare(strict_types=1);

namespace XBase\Record;

use XBase\Enum\FieldType;
use XBase\Header\Column;

/**
 * Visual Foxpro record.
 */
class VisualFoxproRecord extends FoxproRecord
{
    public function get(string $columnName)
    {
        $column = $this->table->getColumn($columnName);

        switch ($column->type) {
            case FieldType::BLOB:
                return $this->getMemo($column);
            case FieldType::GENERAL:
                return $this->data[$column->name];
            default:
                return parent::get($columnName);
        }
    }

    public function set(string $columnName, $value): RecordInterface
    {
        $column = $this->table->getColumn($columnName);
        switch ($column->type) {
            case FieldType::BLOB:
            case FieldType::MEMO:
                return $this->setMemo($column, $value);
            case FieldType::INTEGER:
                return $this->setInt($column, $value);
            case FieldType::DOUBLE:
                return $this->setDouble($column, $value);
            case FieldType::DATETIME:
                return $this->setDateTime($column, $value);
            case FieldType::CURRENCY:
                return $this->setCurrency($column, $value);
            case FieldType::GENERAL:
                return $this->setGeneral($column, $value);
            case FieldType::FLOAT:
                return $this->setFloat($column, $value);
            case FieldType::VAR_FIELD:
            case FieldType::VARBINARY:
                return $this->setVarchar($column, $value);
            default:
                return parent::set($columnName, $value);
        }
    }

    /**
     * @param $value
     */
    protected function setMemo(Column $column, $value): RecordInterface
    {
        $this->checkType($column, [FieldType::BLOB, FieldType::MEMO]);

        if (empty($this->data[$column->name]) && $value) {
            $this->data[$column->name] = $this->table->memo->create($value);
        } elseif (!empty($this->data[$column->name])) {
            $this->data[$column->name] = $this->table->memo->update($this->data[$column->name], $value);
        }

        return $this;
    }

    protected function setGeneral(Column $column, $value): self
    {
        $this->checkType($column, FieldType::GENERAL);

        if (null !== $value) {
            $value = (int) $value;
        }

        $this->data[$column->name] = $value;

        return $this;
    }

    protected function setInt(Column $column, $value): self
    {
        $this->checkType($column, FieldType::INTEGER);

        if (null !== $value) {
            $value = (int) $value;
        }

        $this->data[$column->name] = $value;

        return $this;
    }

    protected function setDouble(Column $column, $value): self
    {
        $this->checkType($column, FieldType::DOUBLE);

        if (is_string($value)) {
            $value = (float) str_replace(',', '.', trim($value));
        }

        $this->data[$column->name] = $value;

        return $this;
    }

    protected function setCurrency(Column $column, $value): self
    {
        $this->checkType($column, FieldType::CURRENCY);

        if (is_string($value)) {
            $value = (float) str_replace(',', '.', trim($value));
        }

        $this->data[$column->name] = $value;

        return $this;
    }

    private function setVarchar(Column $column, $value): self
    {
        $this->checkType($column, [FieldType::VAR_FIELD, FieldType::VARBINARY]);

        $this->data[$column->name] = $value;

        return $this;
    }

    protected function setDateTime(Column $column, $value): self
    {
        $this->checkType($column, FieldType::DATETIME);

        if (is_int($value)) {
            $value = \DateTime::createFromFormat('U', $value);
        } elseif (is_string($value)) {
            $value = new \DateTime($value);
        }

        $this->data[$column->name] = $value;

        return $this;
    }

    /**
     * Get DATE(D) or DATETIME(T) data as object of \DateTime class.
     */
    public function getDateTimeObject(string $columnName): ?\DateTimeInterface
    {
        $column = $this->table->getColumn($columnName);
        $this->checkType($column, [FieldType::DATE, FieldType::DATETIME]);
        if (in_array($column->type, [FieldType::DATETIME])) {
            return $this->get($column->name);
        }

        return parent::getDateTimeObject($column->name);
    }
}
