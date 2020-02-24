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

    public function getGeneral($columnName)
    {
        $data = unpack("L", $this->choppedData[$columnName]);
        return $data[1];
    }

    /**
     * @param string $columnName
     * @param        $length
     *
     * @return bool|float|int
     */
    public function getInt(string $columnName)
    {
        $s = $this->choppedData[$columnName];

        if (!$s) {
            return false;
        }

        if ($this->table->isFoxpro()) {
            $su = unpack("i", $s);
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

    /**
     * @param string $columnName
     *
     * @return int
     */
    public function getDouble($columnName)
    {
        $s = $this->choppedData[$columnName];

        $s = unpack('d', $s);

        if ($s) {
            return $s[1];
        }

        return 0;
    }

    public function getCurrency($columnName)
    {
        $s = $this->choppedData[$columnName];

        $s = unpack('q', $s);

        if ($s) {
            return $s[1] / 10000;
        }

        return 0;
    }

    public function getVarchar($columnName)
    {
        $s = $this->forceGetString($columnName);
        if (false !== ($pos = strpos($s, chr(0x00)))) {
            $s = substr($s, 0, $pos);
        }
        return $s;
    }

    public function getVarbinary($columnName)
    {
        $s = $this->forceGetString($columnName);
        if (false !== ($pos = strpos($s, chr(0x00)))) {
            $s = substr($s, 0, $pos);
        }
        return $s;
    }

    /**
     * @param ColumnInterface $columnObj
     * @param                 $value
     *
     * @return bool
     */
    public function setDateTime($columnObj, $value)
    {
        if ($columnObj->getType() != FieldType::DATETIME) {
            trigger_error($columnObj->getName().' is not a DateTime column', E_USER_ERROR);
        }

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('U');
        }

        if (strlen($value) == 0) {
            $this->forceSetString($columnObj, '');
            return false;
        }

        $a = getdate($value);
        $d = $this->zeroDate + (mktime(0, 0, 0, $a['mon'], $a['mday'], $a['year']) / 86400);
        $d = pack('i', $d);
        $t = pack('i', mktime($a['hours'], $a['minutes'], $a['seconds'], 0, 0, 0));
        $this->choppedData[$columnObj->getColIndex()] = $d.$t;
    }

    /**
     * @param ColumnInterface $columnObj
     * @param                 $value
     *
     * @return bool
     */
    public function setFloat($columnObj, $value)
    {
        if ($columnObj->getType() != FieldType::FLOAT) {
            trigger_error($columnObj->getName().' is not a Float column', E_USER_ERROR);
        }

        if (strlen($value) == 0) {
            $this->forceSetString($columnObj, '');
            return false;
        }

        $value = str_replace(',', '.', $value);
        $this->forceSetString($columnObj, $value);
    }

    /**
     * @param ColumnInterface $columnObj
     * @param                 $value
     *
     * @return bool
     */
    public function setInt($columnObj, $value)
    {
        if ($columnObj->getType() != FieldType::NUMERIC) {
            trigger_error($columnObj->getName().' is not a Number column', E_USER_ERROR);
        }

        if (strlen($value) == 0) {
            $this->forceSetString($columnObj, '');
            return false;
        }

        $value = str_replace(',', '.', $value);
        $this->forceSetString($columnObj, number_format($value, $columnObj->getDecimalCount(), '.', ''));
    }

    /**
     * @return bool|float|int
     */
    public function getDateTime(string $columnName)
    {
        $raw = $this->choppedData[$columnName];
        $buf = unpack('i*', $raw);
        $intDate = $buf[1];
        $inttime = $buf[2];

        if ($intDate == 0 && $inttime == 0) {
            return false;
        }

        $longDate = ($intDate - $this->zeroDate) * 86400;

        return $longDate + ($inttime / 1000);
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
