<?php declare(strict_types=1);

namespace XBase\Column;

use XBase\Header\Column as HeaderColumn;

class XBaseColumn implements ColumnInterface
{
    /**
     * @var HeaderColumn
     */
    private $column;

    public function __construct(HeaderColumn $column)
    {
        $this->column = $column;
    }

    /**
     * @return int
     */
    public function getMemAddress()
    {
        return $this->column->memAddress;
    }

    public function getName(): string
    {
        return $this->column->name;
    }

    public function isSetFields(): ?bool
    {
        return $this->column->setFields;
    }

    public function getType(): string
    {
        return $this->column->type;
    }

    public function getWorkAreaID(): ?int
    {
        return $this->column->workAreaID;
    }

    public function getDecimalCount(): ?int
    {
        return $this->column->decimalCount;
    }

    public function isIndexed(): ?bool
    {
        return $this->column->indexed;
    }

    public function getLength(): int
    {
        return $this->column->length;
    }

    public function getColIndex(): int
    {
        return $this->column->columnIndex;
    }

    public function getBytePos(): int
    {
        return $this->column->bytePosition;
    }

    public function __toString()
    {
        return $this->column->name;
    }

    /**
     * @return string
     */
    public function getRawName(): ?string
    {
        return $this->column->rawName;
    }

    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use getLength()
     */
    public function getDataLength(): int
    {
        return $this->column->length;
    }
}
