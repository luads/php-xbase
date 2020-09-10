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

    public function getObject(ColumnInterface $column)
    {
        switch ($column->getType()) {
            case FieldType::INTEGER:
                return $this->getInt($column->getName());
            case FieldType::DOUBLE:
                return $this->getDouble($column->getName());
            case FieldType::DATETIME:
                return $this->getDateTime($column->getName());
            case FieldType::CURRENCY:
                return $this->getCurrency($column->getName());
            case FieldType::FLOAT:
                return $this->getFloat($column->getName());
            case FieldType::VAR_FIELD:
            case FieldType::VARBINARY:
                return $this->getVarchar($column->getName());
            default:
                return parent::getObject($column);
        }
    }

    public function setObject(ColumnInterface $column, $value)
    {
        switch ($column->getType()) {
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
                return parent::setObject($column, $value);
        }
    }

    public function getGeneral(string $columnName)
    {
        $data = unpack('L', $this->choppedData[$columnName]);
        return $data[1];
    }

    public function setGeneral(ColumnInterface $column, $value): self
    {
        $this->choppedData[$column->getName()] = pack('L', $value);
        return $this;
    }

    /**
     * @return bool|float|int
     */
    public function getInt(string $columnName)
    {
        $s = $this->choppedData[$columnName];

        if (!$s) {
            return false;
        }

        if ($this->table->isFoxpro()) {
            $su = unpack('i', $s);
            $ret = $su[1];
        } else {
            $ret = ord($s[0]);

            $length = $this->getColumn($columnName)->getLength();

            for ($i = 1; $i < $length; $i++) {
                $ret += $i * 256 * ord($s[$i]);
            }
        }

        return $ret;
    }

    public function setInt(ColumnInterface $column, $value)
    {
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

    /**
     * @return int
     */
    public function getDouble(string $columnName)
    {
        $s = $this->choppedData[$columnName];

        $s = unpack('d', $s);

        if ($s) {
            return $s[1];
        }

        return 0;
    }

    public function setDouble(ColumnInterface $column, $value): self
    {
        $this->choppedData[$column->getName()] = pack('d', $value);

        return $this;
    }

    public function getCurrency(string $columnName)
    {
        $s = $this->choppedData[$columnName];

        $s = unpack('q', $s);

        if ($s) {
            return $s[1] / 10000;
        }

        return 0;
    }

    private function setCurrency(ColumnInterface $column, $value): self
    {
        //todo

        return $this;
    }

    public function getVarchar(string $columnName)
    {
        $s = $this->forceGetString($columnName);
        if (false !== ($pos = strpos($s, chr(0x00)))) {
            $s = substr($s, 0, $pos);
        }
        return $s;
    }

    private function setVarchar(ColumnInterface $column, $value): self
    {
        $this->choppedData[$column->getName()] = $value;

        return $this;
    }

    public function getVarbinary(string $columnName)
    {
        $s = $this->forceGetString($columnName);
        if (false !== ($pos = strpos($s, chr(0x00)))) {
            $s = substr($s, 0, $pos);
        }
        return $s;
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
     * @return bool|float|int
     */
    public function getDateTime(string $columnName)
    {
        $raw = $this->choppedData[$columnName];
        $buf = unpack('i*', $raw);
        $intDate = $buf[1];
        $intTime = $buf[2];

        if (0 == $intDate && 0 == $intTime) {
            return false;
        }

        $longDate = ($intDate - $this->zeroDate) * 86400;

        return $longDate + ($intTime / 1000);
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
}
