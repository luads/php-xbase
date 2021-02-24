<?php declare(strict_types=1);

namespace XBase\Header;

use XBase\Column\ColumnInterface;

abstract class AbstractHeader implements HeaderInterface
{
    /**
     * @var int
     */
    public $version;

    /**
     * @var int unixtime
     */
    public $modifyDate;

    /**
     * @var int
     */
    public $recordCount = 0;

    /**
     * @var int
     */
    public $recordByteLength = 20;

    /**
     * @var bool
     */
    public $inTransaction = false;

    /**
     * @var bool
     */
    public $encrypted = false;

    /** @var int */
    public $mdxFlag = 0;

    /**
     * @var int language codepage
     *
     * @see https://blog.codetitans.pl/post/dbf-and-language-code-page/
     */
    public $languageCode;

    /**
     * @var ColumnInterface[]
     */
    public $columns = [];

    /**
     * @var int
     */
    public $length;

    public function addColumn(ColumnInterface $column): self
    {
        $name = $nameBase = $column->getName();
        $index = 0;

        while (isset($this->columns[$name])) {
            $name = $nameBase.++$index;
        }

        $this->columns[$name] = $column;

        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }
}
