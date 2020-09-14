<?php declare(strict_types=1);

namespace XBase\Header;

use XBase\Column\ColumnInterface;
use XBase\Enum\TableType;

abstract class AbstractHeader implements HeaderInterface
{
    /**
     * @var int
     */
    protected $version;

    /**
     * @var int unixtime
     */
    protected $modifyDate;

    /**
     * @var int
     */
    protected $recordCount;

    /**
     * @var int
     */
    protected $recordByteLength;

    /**
     * @var bool
     */
    protected $inTransaction;

    /**
     * @var bool
     */
    protected $encrypted;

    /** @var int */
    protected $mdxFlag;

    /**
     * @var int Language codepage.
     * @see https://blog.codetitans.pl/post/dbf-and-language-code-page/
     */
    protected $languageCode;

    /**
     * @var ColumnInterface[]
     */
    protected $columns;

    /**
     * @var int
     */
    protected $length;

    public function __construct(
        int $version,
        int $modifyDate,
        int $recordCount,
        int $headerLength,
        int $recordByteLength,
        bool $inTransaction,
        bool $encrypted,
        int $mdxFlag,
        int $languageCode
    ) {
        $this->version = $version;
        $this->modifyDate = $modifyDate;
        $this->recordCount = $recordCount;
        $this->length = $headerLength;
        $this->recordByteLength = $recordByteLength;
        $this->inTransaction = $inTransaction;
        $this->encrypted = $encrypted;
        $this->mdxFlag = $mdxFlag;
        $this->languageCode = $languageCode;
    }

    public function addColumn(ColumnInterface $column): HeaderInterface
    {
        $name = $nameBase = $column->getName();
        $index = 0;

        while (isset($this->columns[$name])) {
            $name = $nameBase.++$index;
        }

        $this->columns[$name] = $column;

        return $this;
    }

    public function isFoxpro(): bool
    {
        return TableType::isFoxpro($this->version);
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getModifyDate(): int
    {
        return $this->modifyDate;
    }

    public function getRecordCount(): int
    {
        return $this->recordCount;
    }

    public function getRecordByteLength(): int
    {
        return $this->recordByteLength;
    }

    public function isInTransaction(): bool
    {
        return $this->inTransaction;
    }

    public function isEncrypted(): bool
    {
        return $this->encrypted;
    }

    public function getMdxFlag(): int
    {
        return $this->mdxFlag;
    }

    public function getLanguageCode(): int
    {
        return $this->languageCode;
    }

    /**
     * @return ColumnInterface[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getLength(): int
    {
        return $this->length;
    }
}
