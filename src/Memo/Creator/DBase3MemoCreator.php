<?php declare(strict_types=1);

namespace XBase\Memo\Creator;

use XBase\Stream\Stream;

class DBase3MemoCreator extends AbstractMemoCreator
{
    protected function writeHeader(Stream $stream): void
    {
        $stream->write(pack('V', 1)); //next block
        $stream->write(str_pad('', 4, chr(0))); //reserved
        $stream->write('dBaseII'); //reserved
        $stream->write(str_pad('', 1, chr(0))); //version number
        $stream->write(str_pad('', 496, chr(0))); //garbage
    }
}
