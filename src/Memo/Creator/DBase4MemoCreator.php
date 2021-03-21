<?php declare(strict_types=1);

namespace XBase\Memo\Creator;

use XBase\Stream\Stream;

class DBase4MemoCreator extends AbstractMemoCreator
{
    protected function writeHeader(Stream $stream): void
    {
        $stream->write(pack('V', 1)); //next block
        $stream->write(str_pad('', 4, chr(0))); //reserved
        $stream->write('dBaseII'); //reserved

        $stream->seek(20); //version number
        $stream->writeUShort(512); //blockLengthInBytes
    }
}
