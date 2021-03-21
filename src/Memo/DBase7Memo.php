<?php declare(strict_types=1);

namespace XBase\Memo;

class DBase7Memo extends DBase4Memo
{
    protected function readHeader(): void
    {
        $this->fp->seek(0);
        $this->nextFreeBlock = $this->fp->readUInt();

        $this->fp->seek(20);
        $this->blockLengthInBytes = unpack('S', $this->fp->read(2))[1];
    }
}
