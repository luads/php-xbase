<?php declare(strict_types=1);

namespace XBase\Header\Reader\Column;

use XBase\Enum\FieldType;
use XBase\Header\Column;
use XBase\Stream\Stream;

class DBase7ColumnReader extends AbstractColumnReader
{
    public static function getHeaderLength(): int
    {
        return 48;
    }

    protected function createColumn(string $memoryChunk): Column
    {
        $header = parent::createColumn($memoryChunk);

        $name = (false !== strpos($header->rawName, chr(0x00))) ? substr($header->rawName, 0, strpos($header->rawName, chr(0x00))) : trim($header->rawName);

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
            'reserved1'    => $s->readUShort(), //35-36
            'mdxFlag'      => $s->readUChar(), //37
            'reserved2'    => $s->readUShort(), //38-39
            'nextAI'       => $s->readUInt(), //40-43
            'reserved3'    => $s->read(4), //44-47
        ];
    }
}
