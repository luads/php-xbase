<?php

namespace XBase\Column;

abstract class AbstractColumn implements ColumnInterface
{
    /** @var string */
    protected $name;
    /** @var string */
    protected $rawName;
    /** @var string */
    protected $type;
    /** @var int */
    protected $length;
    /** @var int */
    protected $decimalCount;

    /**@var int Field address within record. */
    protected $memAddress;
    protected $workAreaID;
    protected $setFields;
    protected $indexed;
    /** @var int|null Data starts from index */
    protected $bytePos;
    /** @var int */
    protected $colIndex;

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

    public function getColIndex()
    {
        return $this->colIndex;
    }

    /**
     * @return int
     * @deprecated use getMemAddress
     */
    public function getBytePos()
    {
        return $this->bytePos;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRawName()
    {
        return $this->rawName;
    }

    public function getDataLength()
    {
        return $this->length;
    }
}
