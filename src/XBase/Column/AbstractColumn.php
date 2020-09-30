<?php declare(strict_types=1);

namespace XBase\Column;

abstract class AbstractColumn implements ColumnInterface
{
    /** @var string */
    protected $name;

    /** @var string|null */
    protected $rawName;

    /** @var string */
    protected $type;

    /** @var int */
    protected $length;

    /** @var int|null */
    protected $decimalCount;

    /** @var int Field address within record. */
    protected $memAddress;

    /** @var int|null */
    protected $workAreaID;

    /** @var bool|null */
    protected $setFields = false;

    /** @var bool|null */
    protected $indexed = false;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function isSetFields(): ?bool
    {
        return $this->setFields;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getWorkAreaID(): ?int
    {
        return $this->workAreaID;
    }

    public function getDecimalCount(): ?int
    {
        return $this->decimalCount;
    }

    public function isIndexed(): ?bool
    {
        return $this->indexed;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getColIndex(): int
    {
        return $this->colIndex;
    }

    public function getBytePos(): int
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
    public function getRawName(): ?string
    {
        return $this->rawName;
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use getLength()
     */
    public function getDataLength(): int
    {
        return $this->length;
    }
}
