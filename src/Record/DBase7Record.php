<?php declare(strict_types=1);

namespace XBase\Record;

use XBase\Enum\FieldType;
use XBase\Header\Column;

class DBase7Record extends DBase4Record
{
    public function set(string $columnName, $value): RecordInterface
    {
        $column = $this->table->getColumn($columnName);
        switch ($column->type) {
            case FieldType::TIMESTAMP:
                return $this->setTimestamp($column, $value);
            default:
                parent::set($columnName, $value);
        }

        return $this;
    }

    /**
     * Get DATE(D) or DATETIME(T) data as object of \DateTime class.
     */
    public function getDateTimeObject(string $columnName): ?\DateTimeInterface
    {
        $column = $this->table->getColumn($columnName);
        $this->checkType($column, [FieldType::DATE, FieldType::TIMESTAMP]);

        $data = $this->get($columnName);
        if (in_array($column->type, [FieldType::TIMESTAMP])) {
            return \DateTime::createFromFormat('U', (string) $this->getTimestamp($columnName));
        }

        return new \DateTime($data);
    }

    public function getTimestamp(string $columnName): int
    {
        return $this->get($columnName);
    }

    protected function setTimestamp(Column $column, $value): self
    {
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('U');
        }

        $this->data[$column->name] = (int) $value;

        return $this;
    }
}
