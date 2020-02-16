<?php

namespace XBase;

class Column
{
    /** @var string */
    public $name;
    /** @var string */
    public $rawname;
    /** @var string */
    public $type;
    /** @var int */
    public $length;
    /** @var int */
    public $decimalCount;

    /**
     * @var int Field address within record.
     */
    protected $memAddress;
    protected $workAreaID;
    protected $setFields;
    protected $indexed;
    /**
     * @var int|null
     * @deprecated same as memAddress
     */
    protected $bytePos;
    protected $colIndex;

    /**
     * Column constructor.
     *
     * @param string   $name
     * @param string   $type
     * @param int      $memAddress
     * @param int      $length
     * @param int      $decimalCount
     * @param string   $reserved1
     * @param int      $workAreaID
     * @param string   $reserved2
     * @param bool     $setFields
     * @param string   $reserved3
     * @param int      $indexed
     * @param int      $colIndex
     * @param int|null $bytePos
     */
    public function __construct($name, $type, $memAddress, $length, $decimalCount, $reserved1, $workAreaID, $reserved2, $setFields, $reserved3, $indexed, $colIndex, $bytePos = null)
    {
        $this->rawname = $name;
        // first byte is 'deleted mark'
        $this->name = (strpos($name, chr(0x00)) !== false) ? substr($name, 0, strpos($name, chr(0x00))) : $name;
        $this->type = $type;
        $this->memAddress = $memAddress;
        $this->length = $length;
        $this->decimalCount = $decimalCount;
        $this->workAreaID = $workAreaID;
        $this->setFields = $setFields;
        $this->indexed = $indexed;
        $this->colIndex = $colIndex;
        $this->bytePos = $bytePos;
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
     * @return int
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

    /**
     * @return int
     * @deprecated use getMemAddress
     */
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
