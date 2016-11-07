<?php

namespace XBase;

class Record
{
    const DBFFIELD_TYPE_MEMO = 'M';     // Memo type field
    const DBFFIELD_TYPE_CHAR = 'C';     // Character field
    const DBFFIELD_TYPE_DOUBLE = 'B';   // Double
    const DBFFIELD_TYPE_NUMERIC = 'N';  // Numeric
    const DBFFIELD_TYPE_FLOATING = 'F'; // Floating point
    const DBFFIELD_TYPE_DATE = 'D';     // Date
    const DBFFIELD_TYPE_LOGICAL = 'L';  // Logical - ? Y y N n T t F f (? when not initialized).
    const DBFFIELD_TYPE_DATETIME = 'T'; // DateTime
    const DBFFIELD_TYPE_INDEX = 'I';    // Index
    const DBFFIELD_IGNORE_0 = '0';      // ignore this field

    protected $zerodate = 0x253d8c;
    protected $table;
    protected $choppedData;
    protected $deleted;
    protected $inserted;
    protected $recordIndex;
    protected $memoFile;

    public function __construct(Table $table, $recordIndex, $rawData = false)
    {
        $this->table = $table;
        $this->memoFile = $table->memoFile;
        $this->recordIndex = $recordIndex;
        $this->choppedData = array();

        if ($rawData && strlen($rawData) > 0) {
            $this->inserted = false;
            $this->deleted = (ord($rawData[0]) != '32');

            foreach ($table->getColumns() as $column) {
                $this->choppedData[$column->getName()] = substr($rawData, $column->getBytePos(), $column->getDataLength());
            }
        } else {
            $this->inserted = true;
            $this->deleted = false;

            foreach ($table->getColumns() as $column) {
                $this->choppedData[$column->getName()] = str_pad('', $column->getDataLength(), chr(0));
            }
        }
    }

    public function destroy() {
        $this->table = null;
        $this->choppedData = null;
    }

    public function __get($name)
    {
        return $this->getString($name);
    }

    public function __set($name, $value)
    {
        return $this->setStringByName($name, $value);
    }

    public function isDeleted()
    {
        return $this->deleted;
    }

    public function isInserted()
    {
        return $this->inserted;
    }

    public function getColumns()
    {
        return $this->table->getColumns();
    }

    public function getColumn($name)
    {
        return $this->table->getColumn($name);
    }

    public function getRecordIndex()
    {
        return $this->recordIndex;
    }

    public function getString($columnName)
    {
        $column = $this->table->getColumn($columnName);

        if ($column->getType() == self::DBFFIELD_TYPE_CHAR) {
            return $this->forceGetString($columnName);
        } else {
            $result = $this->getObject($column);

            if ($result && ($column->getType() == self::DBFFIELD_TYPE_DATETIME || $column->getType() == self::DBFFIELD_TYPE_DATE)) {
                return date('r', $result);
            }

            if ($column->getType() == self::DBFFIELD_TYPE_LOGICAL) {
                return $result? '1' : '0';
            }

            return $result;
        }
    }

    public function forceGetString($columnName)
    {
        $data = trim($this->choppedData[$columnName]);

        if ($this->table->getConvertFrom()) {
            $data = iconv($this->table->getConvertFrom(), 'utf-8', $data);
        }

        if (!isset($data[0]) || ord($data[0]) === 0) {
            return null;
        }

        return $data;
    }

    public function getObject(Column $column)
    {
        switch ($column->getType()) {
            case self::DBFFIELD_TYPE_CHAR:return $this->getString($column->getName());
            case self::DBFFIELD_TYPE_DOUBLE:return $this->getDouble($column->getName());
            case self::DBFFIELD_TYPE_DATE:return $this->getDate($column->getName());
            case self::DBFFIELD_TYPE_DATETIME:return $this->getDateTime($column->getName());
            case self::DBFFIELD_TYPE_FLOATING:return $this->getFloat($column->getName());
            case self::DBFFIELD_TYPE_LOGICAL:return $this->getBoolean($column->getName());
            case self::DBFFIELD_TYPE_MEMO:return $this->getMemo($column->getName());
            case self::DBFFIELD_TYPE_NUMERIC:return $this->getNum($column->getName());
            case self::DBFFIELD_TYPE_INDEX:return $this->getIndex($column->getName(), $column->getLength());
            case self::DBFFIELD_IGNORE_0:return false;
        }

        throw new Exception\InvalidColumnException(sprintf('Cannot handle datatype %s', $column->getType()));
    }

    public function getChar($columnName)
    {
        return $this->forceGetString($columnName);
    }

    public function getDate($columnName)
    {
        $s = $this->forceGetString($columnName);

        if (!$s) {
            return false;
        }

        return strtotime($s);
    }

    public function getDateTime($columnName)
    {
        $raw = $this->choppedData[$columnName];
        $buf = unpack('i', substr($raw, 0, 4));
        $intdate = $buf[1];
        $buf = unpack('i', substr($raw, 4, 4));
        $inttime = $buf[1];

        if ($intdate == 0 && $inttime == 0) {
            return false;
        }

        $longdate = ($intdate - $this->zerodate) * 86400;

        return $longdate + ($inttime / 1000);
    }

    public function getBoolean($columnName)
    {
        $s = $this->forceGetString($columnName);

        if (!$s) {
            return false;
        }

        switch (strtoupper($s[0])) {
            case 'T':
            case 'Y':
            case 'J':
            case '1':
                return true;

            default:return false;
        }
    }

    public function getMemo($columnName)
    {
        $data = $this->forceGetString($columnName);
        if($data && strlen($data) == 2) {
            $pointer = unpack('s', $data)[1];
            return $this->memoFile->get($pointer);
        } else {
            return $data;
        }
    }

    public function getDouble($columnName)
    {
        $s = $this->choppedData[$columnName];

        $s = unpack('d', $s);

        if ($s) {
            return $s[1];
        }

        return 0;
    }

    public function getFloat($columnName)
    {
        $s = $this->choppedData[$columnName];

        $s = unpack('f', $s);

        if ($s) {
            return $s[1];
        }

        return 0;
    }

    public function getNum($columnName)
    {
        $s = $this->forceGetString($columnName);

        if (!$s) {
            return false;
        }

        $s = str_replace(',', '.', $s);

        $column = $this->getColumn($columnName);

        if ($column->type == Record::DBFFIELD_TYPE_NUMERIC &&
            ($column->getDecimalCount() > 0 || $column->length > 9)
        )
            return doubleval($s);
        else
            return intval($s);
    }

    public function getIndex($columnName, $length)
    {
        $s = $this->choppedData[$columnName];

        if (!$s) {
            return false;
        }

        if($this->table->foxpro) {
            $su = unpack("i", $s);
            $ret = $su[1];
        } else {
            $ret = ord($s[0]);

            for ($i = 1; $i < $length; $i++) {
                $ret += $i * 256 * ord($s[$i]);
            }
        }

        return $ret;
    }

    public function copyFrom($record)
    {
        $this->choppedData = $record->choppedData;
    }

    public function setDeleted($b)
    {
        $this->deleted = $b;
    }

    public function setStringByName($columnName, $value)
    {
        $this->setString($this->table->getColumn($columnName), $value);
    }

    public function setStringByIndex($columnIndex, $value)
    {
        $this->setString($this->table->getColumn($columnIndex), $value);
    }

    public function setString($columnObj, $value)
    {
        if ($columnObj->getType() == self::DBFFIELD_TYPE_CHAR) {
            $this->forceSetString($columnObj, $value);
        } else {
            if ($columnObj->getType() == self::DBFFIELD_TYPE_DATETIME || $columnObj->getType() == self::DBFFIELD_TYPE_DATE) {
                $value = strtotime($value);
            }

            $this->setObject($columnObj, $value);
        }
    }

    public function forceSetString($columnObj, $value)
    {
        $this->choppedData[$columnObj->getName()] = str_pad(substr($value, 0, $columnObj->getDataLength()), $columnObj->getDataLength(), " ");
    }

    public function setObjectByName($columnName, $value)
    {
        return $this->setObject($this->table->getColumn($columnName), $value);
    }

    public function setObjectByIndex($columnIndex, $value)
    {
        return $this->setObject($this->table->getColumn($columnIndex), $value);
    }

    public function setObject($columnObj, $value)
    {
        switch ($columnObj->getType()) {
            case self::DBFFIELD_TYPE_CHAR:
                $this->setString($columnObj, $value);
                return false;
            case self::DBFFIELD_TYPE_DOUBLE:
                $this->setFloat($columnObj, $value);
                return false;
            case self::DBFFIELD_TYPE_DATE:
                $this->setDate($columnObj, $value);
                return false;
            case self::DBFFIELD_TYPE_DATETIME:
                $this->setDateTime($columnObj, $value);
                return false;
            case self::DBFFIELD_TYPE_FLOATING:
                $this->setFloat($columnObj, $value);
                return false;
            case self::DBFFIELD_TYPE_LOGICAL:
                $this->setBoolean($columnObj, $value);
                return false;
            case self::DBFFIELD_TYPE_MEMO:
                $this->setMemo($columnObj, $value);
                return false;
            case self::DBFFIELD_TYPE_NUMERIC:
                $this->setInt($columnObj, $value);
                return false;
            case self::DBFFIELD_IGNORE_0:
                return false;
        }

        trigger_error('cannot handle datatype' . $columnObj->getType(), E_USER_ERROR);
    }

    public function setDate($columnObj, $value)
    {
        if ($columnObj->getType() != self::DBFFIELD_TYPE_DATE) {
            trigger_error($columnObj->getName() . ' is not a Date column', E_USER_ERROR);
        }

        if (strlen($value) == 0) {
            $this->forceSetString($columnObj, '');
            return false;
        }

        $this->forceSetString($columnObj, date('Ymd', $value));
    }

    public function setDateTime($columnObj, $value)
    {
        if ($columnObj->getType() != self::DBFFIELD_TYPE_DATETIME) {
            trigger_error($columnObj->getName() . ' is not a DateTime column', E_USER_ERROR);
        }

        if (strlen($value) == 0) {
            $this->forceSetString($columnObj, '');
            return false;
        }

        $a = getdate($value);
        $d = $this->zerodate + (mktime(0, 0, 0, $a['mon'], $a['mday'], $a['year']) / 86400);
        $d = pack('i', $d);
        $t = pack('i', mktime($a['hours'], $a['minutes'], $a['seconds'], 0, 0, 0));
        $this->choppedData[$columnObj->getColIndex()] = $d . $t;
    }

    public function setBoolean($columnObj, $value)
    {
        if ($columnObj->getType() != self::DBFFIELD_TYPE_LOGICAL) {
            trigger_error($columnObj->getName() . ' is not a DateTime column', E_USER_ERROR);
        }

        switch (strtoupper($value)) {
            case 'T':
            case 'Y':
            case 'J':
            case '1':
            case 'F':
            case 'N':
            case '0':
                $this->forceSetString($columnObj, $value);
                return false;
            case true:
                $this->forceSetString($columnObj, 'T');
                return false;
            default:
                $this->forceSetString($columnObj, 'F');
        }
    }

    public function setMemo($columnObj, $value)
    {
        if ($columnObj->getType() != self::DBFFIELD_TYPE_MEMO) {
            trigger_error($columnObj->getName() . ' is not a Memo column', E_USER_ERROR);
        }

        $this->forceSetString($columnObj, $value);
    }

    public function setFloat($columnObj, $value)
    {
        if ($columnObj->getType() != self::DBFFIELD_TYPE_FLOATING) {
            trigger_error($columnObj->getName() . ' is not a Float column', E_USER_ERROR);
        }

        if (strlen($value) == 0) {
            $this->forceSetString($columnObj, '');
            return false;
        }

        $value = str_replace(',', '.', $value);
        $this->forceSetString($columnObj, $value);
    }

    public function setInt($columnObj, $value)
    {
        if ($columnObj->getType() != self::DBFFIELD_TYPE_NUMERIC) {
            trigger_error($columnObj->getName() . ' is not a Number column', E_USER_ERROR);
        }

        if (strlen($value) == 0) {
            $this->forceSetString($columnObj, '');
            return false;
        }

        $value = str_replace(',', '.', $value);
        $this->forceSetString($columnObj, number_format($value, $columnObj->getDecimalCount()));
    }

    public function serializeRawData()
    {
        return ($this->deleted ? '*' : ' ') . implode('', $this->choppedData);
    }
}
