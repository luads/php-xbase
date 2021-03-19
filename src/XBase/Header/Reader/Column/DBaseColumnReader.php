<?php declare(strict_types=1);

namespace XBase\Header\Reader\Column;

use XBase\Enum\FieldType;
use XBase\Header\Column;
use XBase\Header\Specification\HeaderSpecificationFactory;
use XBase\Header\Specification\Specification;
use XBase\Stream\Stream;

class DBaseColumnReader extends AbstractColumnReader
{
    protected function getSpecification(): Specification
    {
        return HeaderSpecificationFactory::create();
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
            'rawName'      => $s->read(11), //0-10
            'type'         => $s->read(), //11
            'memAddress'   => $s->readUInt(), //12-15
            'length'       => $s->readUChar(), //16
            'decimalCount' => $s->readUChar(), //17
            'reserved1'    => $s->read(2), //18-19
            'workAreaID'   => $s->readUChar(), //20
            'reserved2'    => $s->read(2), //21-22
            'setFields'    => 0 !== $s->readUChar(), //23
            'reserved3'    => $s->read(7), //24-30
            'indexed'      => 0 !== $s->readUChar(), //31
        ];
    }
}
