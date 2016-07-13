<?php 

namespace XBase;

class Column 
{

    public $name;
    public $rawname;
    public $type;
    public $length;
    public $decimalCount;

    protected $memAddress;
    protected $workAreaID;
    protected $setFields;
    protected $indexed;
    protected $bytePos;
    protected $colIndex;

    public function __construct($name, $type, $memAddress, $length, $decimalCount, $reserved1, $workAreaID, $reserved2, $setFields, $reserved3, $indexed, $colIndex, $bytePos) 
    {
        $this->rawname = $name;
        $this->name = (strpos($name, 0x00) !== false ) ? substr($name, 0, strpos($name, 0x00)) : $name;
        $this->type = $type;
        $this->memAddress = $memAddress;
        $this->length = $length;
        $this->decimalCount = $decimalCount;
        $this->workAreaID = $workAreaID;
        $this->setFields = $setFields;
        $this->indexed = $indexed;
        $this->bytePos = $bytePos;
        $this->colIndex = $colIndex;
    }

    
    public function getDecimalCount() 
    {
        return $this->decimalCount;
    }
    
    public function isIndexed() 
    {
        return $this->indexed;
    }
    
    public function getLength() 
    {
        return $this->length;
    }
    
    public function getDataLength() 
    {
        switch ($this->type) {
            case Record::DBFFIELD_TYPE_DATE : return 8;
            case Record::DBFFIELD_TYPE_DATETIME : return 8;
            case Record::DBFFIELD_TYPE_LOGICAL : return 1;
            case Record::DBFFIELD_TYPE_MEMO : return 10;
            default : return $this->length;
        }
    }
    
    public function getMemAddress() 
    {
        return $this->memAddress;
    }
    
    public function getName() 
    {
        return $this->name;
    }
    
    public function isSetFields() 
    {
        return $this->setFields;
    }
    
    public function getType() 
    {
        return $this->type;
    }
    
    public function getWorkAreaID() 
    {
        return $this->workAreaID;
    }
    
    public function toString() 
    {
        return $this->name;
    }
    
    public function getBytePos() 
    {
        return $this->bytePos;
    }
    
    public function getRawname() 
    {
        return $this->rawname;
    }
    
    public function getColIndex() 
    {
        return $this->colIndex;
    }
}
