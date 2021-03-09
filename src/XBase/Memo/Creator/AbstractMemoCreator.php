<?php declare(strict_types=1);

namespace XBase\Memo\Creator;

use XBase\Stream\Stream;
use XBase\Table\Table;

abstract class AbstractMemoCreator implements MemoCreatorInterface
{
    /** @var Table */
    private $table;

    abstract protected function writeHeader(Stream $stream): void;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public static function getExtension(): string
    {
        return 'dbt';
    }

    public function createFile(): string
    {
        $pi = pathinfo($this->table->filepath);
        $memoFilepath = sprintf('%s/%s.%s', $pi['dirname'], $pi['filename'], self::getExtension());

        $stream = Stream::createFromFile($memoFilepath, 'wb+');
        $this->writeHeader($stream);
        $stream->flush();
        $stream->close();

        return $memoFilepath;
    }
}
