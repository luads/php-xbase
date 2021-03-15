<?php declare(strict_types=1);

namespace XBase\Record;

use XBase\Enum\FieldType;

class DBase7Record extends DBase4Record
{
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
}
