<?php declare(strict_types=1);

namespace XBase\Header\Reader\Column;

use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Header\Column;
use XBase\Header\Specification\HeaderSpecificationFactory;
use XBase\Header\Specification\Specification;
use XBase\Stream\Stream;

class DBase7ColumnReader extends AbstractColumnReader
{
    protected function getSpecification(): Specification
    {
        return HeaderSpecificationFactory::create(TableType::DBASE_7_MEMO);
    }

    protected function createColumn(string $memoryChunk): Column
    {
        $header = parent::createColumn($memoryChunk);

        $nameEndIndex = strpos($header->rawName, chr(0x00));
        $name = (false !== $nameEndIndex) ? substr($header->rawName, 0, $nameEndIndex) : trim($header->rawName);

        // chop all garbage from 0x00
        $header->name = strtolower($name);

        if (in_array($header->type, [FieldType::CHAR, FieldType::MEMO])) {
            $header->length = $header->length + 256 * $header->decimalCount;
            $header->decimalCount = null;
        }

        return $header;
    }

    protected function extractArgs(string $memoryChunk): array
    {
        $s = Stream::createFromString($memoryChunk);

        return [
            'rawName'      => $s->read(32), //0-31
            'type'         => $s->read(), //32
            'length'       => $s->readUChar(), //33
            'decimalCount' => $s->readUChar(), //34
            'reserved1'    => $s->read(2), //35-36
            'mdxFlag'      => $s->readUChar(), //37
            'reserved2'    => $s->read(2), //38-39
            'nextAI'       => $s->readUInt(), //40-43
            'reserved3'    => $s->read(4), //44-47
        ];
    }
}
