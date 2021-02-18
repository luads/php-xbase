<?php declare(strict_types=1);

namespace XBase\Header;

use XBase\Column\ColumnInterface;

interface HeaderInterface
{
    public function getVersion(): int;

    public function isFoxpro(): bool;

    public function getModifyDate();

    /**
     * @return int header length
     */
    public function getLength(): int;

    public function getRecordCount(): int;

    public function setRecordCount(int $recordCount): self;

    public function increaseRecordCount(int $inc = 1);

//    public function setRecordCount(int $count): self;

    public function getRecordByteLength(): int;

    public function isInTransaction(): bool;

    public function isEncrypted(): bool;

    public function getMdxFlag(): int;

    public function addColumn(ColumnInterface $column): self;

    /**
     * @return ColumnInterface[]
     */
    public function getColumns(): array;
}
