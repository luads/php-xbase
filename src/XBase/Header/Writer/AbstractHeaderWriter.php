<?php declare(strict_types=1);

namespace XBase\Header\Writer;

use XBase\Header\Header;
use XBase\Header\Writer\Column\ColumnWriterFactory;
use XBase\Stream\StreamWrapper;

abstract class AbstractHeaderWriter implements HeaderWriterInterface
{
    /** @var StreamWrapper */
    protected $fp;

    public function __construct(StreamWrapper $fp)
    {
        $this->fp = $fp;
    }

    public function write(Header $header): void
    {
        $this->fp->seek(0);

        $this->writeFirstBlock($header);
        $this->writeColumns($header);
        $this->writeRest($header);
    }

    protected function writeFirstBlock(Header $header): void
    {
        $this->fp->writeUChar($header->version); //0
        $this->fp->write3ByteDate(time()); //1-3
        $this->fp->writeUInt($header->recordCount); //4-7
        $this->fp->writeUShort($header->length); //8-9
        $this->fp->writeUShort($header->recordByteLength); //10-11
        $this->fp->write(str_pad('', 2, chr(0))); //12-13
        $this->fp->write(chr($header->inTransaction ? 1 : 0)); //14
        $this->fp->write(chr($header->encrypted ? 1 : 0)); //15
        $this->fp->write(str_pad('', 4, chr(0))); //16-19 //todo-different-table
        $this->fp->write(str_pad('', 8, chr(0))); //20-27 //todo-different-table
        $this->fp->write(chr($header->mdxFlag)); //28
        $this->fp->write(chr($header->languageCode)); //29
        $this->fp->write(str_pad('', 2, chr(0))); //30-31 //todo-different-table
    }

    protected function writeColumns(Header $header): void
    {
        $columnWriter = ColumnWriterFactory::create($header->version);
        foreach ($header->columns as $column) {
            $columnWriter->write($this->fp, $column);
        }
    }

    protected function writeRest(Header $header): void
    {
        $this->fp->writeUChar(0x0d);
    }
}
