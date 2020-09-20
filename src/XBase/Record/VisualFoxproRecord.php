<?php

namespace XBase\Record;

use XBase\Column\ColumnInterface;
use XBase\Enum\FieldType;

/**
 * Visual Foxpro record
 */
class VisualFoxproRecord extends FoxproRecord
{
    /** @var int */
    protected $zeroDate = 0x253d8c;

//    public function getObject(ColumnInterface $column)
//    {
//        switch ($column->getType()) {
//            case FieldType::INTEGER:
//                return $this->getInt($column->getName());
//            case FieldType::DOUBLE:
//                return $this->getDouble($column->getName());
//            case FieldType::DATETIME:
//                return $this->getDateTime($column->getName());
//            case FieldType::CURRENCY:
//                return $this->getCurrency($column->getName());
//            case FieldType::FLOAT:
//                return $this->getFloat($column->getName());
//            case FieldType::VAR_FIELD:
//            case FieldType::VARBINARY:
//                return $this->getVarchar($column->getName());
//            default:
//                return parent::getObject($column);
//        }
//    }

    public function setObject($column, $value)
    {
        $column = $this->toColumn($column);
        switch ($column->getType()) {
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
                return parent::setObject($column->getName(), $value);
        }
    }

    public function setGeneral(ColumnInterface $column, $value): self
    {
        $this->choppedData[$column->getName()] = pack('L', $value);
        return $this;
    }

    public function setInt($columnName, $value)
    {
        $column = $this->toColumn($columnName);
        if (FieldType::INTEGER !== $column->getType()) {
            trigger_error($column->getName().' is not a Number column', E_USER_ERROR);
        }

        if ($this->table->isFoxpro()) {
            $this->choppedData[$column->getName()] = pack('i', $value);
        } else {
            //todo
        }

        return $this;
    }

    public function setDouble(ColumnInterface $column, $value): self
    {
        $this->choppedData[$column->getName()] = pack('d', $value);

        return $this;
    }

    private function setCurrency(ColumnInterface $column, $value): self
    {
        //todo

        return $this;
    }

    private function setVarchar($columnName, $value): self
    {
        $column = $this->toColumn($columnName);
        $this->data[$column->getName()] = $value;
        $this->choppedData[$column->getName()] = $value; //todo-delete

        return $this;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function setFloat(ColumnInterface $column, $value)
    {
        if (FieldType::FLOAT !== $column->getType()) {
            trigger_error($column->getName().' is not a Float column', E_USER_ERROR);
        }

        if (0 == strlen($value)) {
            $this->forceSetString($column, '');
            return false;
        }

        $value = str_replace(',', '.', $value);
        $this->forceSetString($column, $value);
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function setDateTime(ColumnInterface $column, $value)
    {
        if (FieldType::DATETIME !== $column->getType()) {
            trigger_error($column->getName().' is not a DateTime column', E_USER_ERROR);
        }

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('U');
        }

        if (0 == strlen($value)) {
            $this->forceSetString($column, '');
            return false;
        }

        $a = getdate($value);
        $intDate = $this->zeroDate + (mktime(0, 0, 0, $a['mon'], $a['mday'], $a['year']) / 86400);
        $intTime = ($a['hours'] * 3600 + $a['minutes'] * 60 + $a['seconds']) * 1000;
        $this->choppedData[$column->getName()] = pack('i', $intDate).pack('i', $intTime);
    }

    /**
     * Get DATE(D) or DATETIME(T) data as object of \DateTime class
     */
    public function getDateTimeObject(string $columnName): \DateTime
    {
        $column = $this->getColumn($columnName);
        if (!in_array($column->getType(), [FieldType::DATE, FieldType::DATETIME])) {
            trigger_error($column->getName().' is not a Date or DateTime column', E_USER_ERROR);
        }

        $data = $this->forceGetString($columnName);
        if (in_array($column->getType(), [FieldType::DATETIME])) {
            return \DateTime::createFromFormat('U', $this->getDateTime($columnName));
        }

        return new \DateTime($data);
    }

    /**
     * @deprecated use get()
     */
    public function getGeneral(string $columnName)
    {
        return $this->get($columnName);
    }

    /**
     * @deprecated use get()
     */
    public function getDateTime(string $columnName)
    {
        return $this->get($columnName);
    }

    /**
     * @deprecated use get()
     */
    public function getVarbinary(string $columnName)
    {
        return $this->get($columnName);
    }

    /**
     * @deprecated use get()
     */
    public function getVarchar(string $columnName)
    {
        return $this->get($columnName);
    }

    /**
     * @deprecated use get()
     */
    public function getCurrency(string $columnName)
    {
        return $this->get($columnName);
    }

    /**
     * @deprecated use get()
     */
    public function getDouble(string $columnName)
    {
        return $this->data[$columnName];
    }

    /**
     * @deprecated use get()
     */
    public function getInt(string $columnName)
    {
        return $this->data[$columnName];
    }
}
