<?php declare(strict_types=1);

namespace XBase\Table;

use XBase\Header\Header;
use XBase\Memo\MemoInterface;
use XBase\Stream\Stream;

trait TableAwareTrait
{
    /**
     * @var Table
     */
    protected $table;

    protected function getHeader(): Header
    {
        return $this->table->header;
    }

    protected function getMemo(): ?MemoInterface
    {
        return $this->table->memo;
    }

    protected function getStream(): Stream
    {
        return $this->table->stream;
    }
}
