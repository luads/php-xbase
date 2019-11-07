<?php

namespace XBase;

class Column
{
    /** @var string */
    public $name;
    /** @var string */
    public $rawname;
    /** @var  */
    public $type;
    /** @var int */
    public $length;
    /** @var int */
    public $decimalCount;

    protected $memAddress;
    protected $workAreaID;
    protected $setFields;
    protected $indexed;
    protected $bytePos;
    protected $colIndex;

    /**
     * Column constructor.
     *
     * @param string $name
     * @param string $type
     * @param $memAddress
     * @param $length
     * @param $decimalCount
     * @param $reserved1
     * @param $workAreaID
     * @param $reserved2
     * @param $setFields
     * @param $reserved3
     * @param $indexed
     * @param $colIndex
     * @param $bytePos
     */
    public function __construct($name, $type, $memAddress, $length, $decimalCount, $reserved1, $workAreaID, $reserved2, $setFields, $reserved3, $indexed, $colIndex, $bytePos)
    {
        $this->rawname = $name;
        $this->name = (strpos($name, chr(0x00)) !== false) ? substr($name, 0, strpos($name, chr(0x00))) : $name;
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

    /**
     * @return mixed
     */
    public function getDecimalCount()
    {
        return $this->decimalCount;
    }

    /**
     * @return bool
     */
    public function isIndexed()
    {
        return $this->indexed;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return int
     */
    public function getDataLength()
    {
        switch ($this->type) {
            case Record::DBFFIELD_TYPE_DATE:
            case Record::DBFFIELD_TYPE_DATETIME:
                return 8;
            case Record::DBFFIELD_TYPE_LOGICAL:
                return 1;
            case Record::DBFFIELD_TYPE_MEMO:
                return 10;
            default:
                return $this->length;
        }
    }

    /**
     * @return mixed
     */
    public function getMemAddress()
    {
        return $this->memAddress;
    }

    /**
     * @return bool|string
     */
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

    /**
     * @return bool|string
     */
    public function toString()
    {
        return $this->name;
    }

    public function getBytePos()
    {
        return $this->bytePos;
    }

    /**
     * @return string
     */
    public function getRawname()
    {
        return $this->rawname;
    }

    public function getColIndex()
    {
        return $this->colIndex;
    }
}
