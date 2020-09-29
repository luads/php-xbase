<?php declare(strict_types=1);

namespace XBase\Record;

use XBase\Enum\FieldType;

class DBase7Record extends DBase4Record
{
    /**
     * Get DATE(D) or DATETIME(T) data as object of \DateTime class.
     */
    public function getDateTimeObject($columnName): ?\DateTimeInterface
    {
        $column = $this->getColumn($columnName);
        $this->checkType($column, [FieldType::DATE, FieldType::TIMESTAMP]);

        $data = $this->get($columnName);
        if (in_array($column->getType(), [FieldType::TIMESTAMP])) {
            return \DateTime::createFromFormat('U', (string) $this->getTimestamp($columnName));
        }

        return new \DateTime($data);
    }

    public function getTimestamp($columnName): int
    {
        return $this->get($columnName);
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use get()
     */
    public function getInt(string $columnName): int
    {
        return $this->get($columnName);
    }
}
