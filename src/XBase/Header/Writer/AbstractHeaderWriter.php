<?php declare(strict_types=1);

namespace XBase\Header\Writer;

use XBase\Header\HeaderInterface;
use XBase\Stream\StreamWrapper;

abstract class AbstractHeaderWriter implements HeaderWriterInterface
{
    /** @var StreamWrapper */
    protected $fp;

    public function __construct(StreamWrapper $fp)
    {
        $this->fp = $fp;
    }

    public function write(HeaderInterface $header): void
    {
        $this->fp->seek(0);

        $this->writeFirstBlock($header);
        $this->writeColumns($header);
        $this->writeRest($header);
    }

    protected function writeFirstBlock(HeaderInterface $header): void
    {
        $this->fp->writeUChar($header->getVersion()); //0
        $this->fp->write3ByteDate(time()); //1-3
        $this->fp->writeUInt($header->getRecordCount()); //4-7
        $this->fp->writeUShort($header->getLength()); //8-9
        $this->fp->writeUShort($header->getRecordByteLength()); //10-11
        $this->fp->write(str_pad('', 2, chr(0))); //12-13
        $this->fp->write(chr($header->isInTransaction() ? 1 : 0)); //14
        $this->fp->write(chr($header->isEncrypted() ? 1 : 0)); //15
        $this->fp->write(str_pad('', 4, chr(0))); //16-19 //todo-different-table
        $this->fp->write(str_pad('', 8, chr(0))); //20-27 //todo-different-table
        $this->fp->write(chr($header->getMdxFlag())); //28
        $this->fp->write(chr($header->getLanguageCode())); //29
        $this->fp->write(str_pad('', 2, chr(0))); //30-31 //todo-different-table
    }

    protected function writeColumns(HeaderInterface $header): void
    {
        foreach ($header->getColumns() as $column) {
            $column->toBinaryString($this->fp);
        }
    }

    protected function writeRest(HeaderInterface $header): void
    {
        $this->fp->writeUChar(0x0d);
    }
}
