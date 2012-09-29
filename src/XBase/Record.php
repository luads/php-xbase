<?php 

namespace XBase;

class Record 
{
	const DBFFIELD_TYPE_MEMO = 'M';		// Memo type field.
	const DBFFIELD_TYPE_CHAR = 'C';		// Character field.
	const DBFFIELD_TYPE_NUMERIC = 'N';	// Numeric
	const DBFFIELD_TYPE_FLOATING = 'F';	// Floating point
	const DBFFIELD_TYPE_DATE = 'D';		// Date
	const DBFFIELD_TYPE_LOGICAL = 'L';	// Logical - ? Y y N n T t F f (? when not initialized).
	const DBFFIELD_TYPE_DATETIME = 'T';	// DateTime
	const DBFFIELD_TYPE_INDEX = 'I';    // Index 
	const DBFFIELD_IGNORE_0 = '0';		// ignore this field

    protected $zerodate = 0x253d8c;
    protected $table;
    protected $choppedData;
    protected $deleted;
    protected $inserted;
    protected $recordIndex;
    
    public function __construct(Table $table, $recordIndex, $rawData = false) 
    {
        $this->table = $table;
        $this->recordIndex = $recordIndex;
        $this->choppedData = array();

        if ($rawData && strlen($rawData) > 0) {
	        $this->inserted = false;
        	$this->deleted = (ord($rawData[0]) != '32');

        	foreach ($table->getColumns() as $column) {
            	$this->choppedData[] = substr($rawData, $column->getBytePos(), $column->getDataLength());
        	}
    	} else {
	    	$this->inserted = true;
	    	$this->deleted = false;

	    	foreach ($table->getColumns() as $column) {
		    	$this->choppedData[] = str_pad('', $column->getDataLength(), chr(0));
	    	}
    	}
    }

    public function __get($name)
    {
    	return $this->getStringByName($name);
    }

    public function isDeleted() 
    {
        return $this->deleted;
    }
    
    public function getColumns() 
    {
        return $this->table->getColumns();
    }
    
    public function getColumnByName($name) 
    {
        return $this->table->getColumnByName($name);
    }
    
    public function getColumn($index) 
    {
        return $this->table->getColumn($index);
    }
    
    public function getColumnIndex($name) 
    {
        return $this->table->getColumnIndex($name);
    }
    
    public function getRecordIndex() 
    {
        return $this->recordIndex;
    }

    public function getStringByName($columnName) 
    {
        return $this->getString($this->table->getColumnByName($columnName));
    }

    public function getStringByIndex($columnIndex) 
    {
        return $this->getString($this->table->getColumn($columnIndex));
    }

    public function getString(Column $column) 
    {
        if ($column->getType() == self::DBFFIELD_TYPE_CHAR) {
            return $this->forceGetString($column);
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

    public function forceGetString(Column $column) 
    {
        if (ord($this->choppedData[$column->getColIndex()][0]) == '0') {
        	return false;
        }

        return trim($this->choppedData[$column->getColIndex()]);
    }

    public function getObjectByName($columnName) 
    {
        return $this->getObject($this->table->getColumnByName($columnName));
    }

    public function getObjectByIndex($columnIndex) 
    {
        return $this->getObject($this->table->getColumn($columnIndex));
    }

    public function getObject(Column $column) 
    {
        switch ($column->getType()) {
            case self::DBFFIELD_TYPE_CHAR : return $this->getString($column);
            case self::DBFFIELD_TYPE_DATE : return $this->getDate($column);
            case self::DBFFIELD_TYPE_DATETIME : return $this->getDateTime($column);
            case self::DBFFIELD_TYPE_FLOATING : return $this->getFloat($column);
            case self::DBFFIELD_TYPE_LOGICAL : return $this->getBoolean($column);
            case self::DBFFIELD_TYPE_MEMO : return $this->getMemo($column);
            case self::DBFFIELD_TYPE_NUMERIC : return $this->getInt($column);
            case self::DBFFIELD_TYPE_INDEX : return $this->getIndex($column); 
            case self::DBFFIELD_IGNORE_0 : return false;
        }

        throw new Exception\InvalidColumnException(sprintf('Cannot handle datatype %s', $column->getType()));
    }

    public function getDate(Column $column) 
    {
	    if ($column->getType()!=self::DBFFIELD_TYPE_DATE) {
	    	throw new Exception\InvalidColumnException(sprintf('%s is not a Date column', $column->getName()));
	    }

        $s = $this->forceGetString($column);

        if (!$s) {
       		return false;	
        }
        
        return strtotime($s);
    }

    public function getDateTime(Column $column) 
    {
        if ($column->getType()!=self::DBFFIELD_TYPE_DATETIME) {
        	throw new Exception\InvalidColumnException(sprintf('%s is not a DateTime column', $column->getName()));
        }

        $raw =  $this->choppedData[$column->getColIndex()];
        $buf = unpack('i',substr($raw,0,4));
        $intdate = $buf[1];
        $buf = unpack('i',substr($raw,4,4));
        $inttime = $buf[1];

        if ($intdate == 0 && $inttime == 0) {
        	return false;
        }

        $longdate = ($intdate - $this->zerodate) * 86400;

        return $longdate + $inttime;
    }

    public function getBoolean(Column $column) 
    {
        if ($column->getType() != self::DBFFIELD_TYPE_LOGICAL) {
        	throw new Exception\InvalidColumnException(sprintf('%s is not a Boolean column', $column->getName()));
        }

        $s = $this->forceGetString($column);

        if (!$s) {
        	return false;
        }
        
        switch (strtoupper($s[0])) {
            case 'T':
            case 'Y':
            case 'J':
            case '1':
                return true;

            default: return false;
        }
    }

    public function getMemo(Column $column) 
    {
	    if ($column->getType() != self::DBFFIELD_TYPE_MEMO) {
	    	throw new Exception\InvalidColumnException(sprintf('%s is not a Memo column', $column->getName()));
	    }

        return $this->forceGetString($column);
    }

    public function getFloat(Column $column) 
    {
	    if ($column->getType() != self::DBFFIELD_TYPE_FLOATING) {
	    	throw new Exception\InvalidColumnException(sprintf('%s is not a Float column', $column->getName()));
	    }

        $s = $this->forceGetString($column);

        if (!$s) {
        	return false;
        }
        
        $s = str_replace(',', '.', $s);

        return floatval($s);
    }

    public function getInt(Column $column) 
    {
	    if ($column->getType() != self::DBFFIELD_TYPE_NUMERIC) {
	    	throw new Exception\InvalidColumnException(sprintf('%s is not a Number column', $column->getName()));
	    }

        $s = $this->forceGetString($column);

        if (!$s) {
        	return false;
        }
        
        $s = str_replace(',', '.', $s);

        return intval($s);
    }

	public function getIndex(Column $column) 
	{
		if ($column->getType() != self::DBFFIELD_TYPE_INDEX) {
			throw new Exception\InvalidColumnException(sprintf('%s is not a Index column', $column->getName()));
		}

		$s = $this->choppedData[$column->getColIndex()];

		if (!$s) {
        	return false;
        }
		
		$ret = ord($s[0]);

		for ($i = 1; $i < $column->length; $i++) {
			$ret += $i * 256 * ord($s[$i]);
		}

		return $ret;   
	}

    protected function serializeRawData() 
    {
	    return ($this->deleted ? '*' : ' ') . implode('', $this->choppedData);
    }
}