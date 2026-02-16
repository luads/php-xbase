<?php

declare(strict_types=1);

namespace XBase\Memo\Creator;

use XBase\Stream\Stream;

class FoxProMemoCreator extends AbstractMemoCreator
{
    public static function getExtension(): string
    {
        return 'fpt';
    }

    protected function writeHeader(Stream $stream): void
    {
        $stream->write(pack('N', 8)); //next block

        $stream->seek(4);
        $stream->write(str_pad('', 2, chr(0))); //reserved

        $stream->seek(6);
        $stream->write(pack('n', 64)); //Block size. 8 * 64 == 512, which will start records after the header
    }
}
