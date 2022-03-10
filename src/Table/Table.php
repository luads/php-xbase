<?php declare(strict_types=1);

namespace XBase\Table;

use XBase\Header\Column;
use XBase\Header\Header;
use XBase\Memo\MemoInterface;
use XBase\Stream\Stream;

class Table
{
    /**
     * @var string
     */
    public $filepath;

    /**
     * @var array
     */
    public $options = [
        'encoding' => null,
        'editMode' => null,
    ];

    /**
     * @var Header
     */
    public $header;

    /**
     * @var Stream
     */
    public $stream;

    /**
     * @var MemoInterface|null
     */
    public $memo;

    /**
     * @var array
     */
    public $handlers = [];

    public function getVersion()
    {
        return $this->header->version;
    }

    public function getColumn(string $name): Column
    {
        $name = strtolower($name);
        foreach ($this->header->columns as $column) {
            if ($column->name === $name) {
                return $column;
            }
        }

        throw new \Exception("Column $name not found");
    }
}
