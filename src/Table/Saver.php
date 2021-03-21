<?php declare(strict_types=1);

namespace XBase\Table;

use XBase\Header\Writer\HeaderWriterFactory;

class Saver
{
    use TableAwareTrait;

    const END_OF_FILE_MARKER = 0x1a;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public function save(): self
    {
        $this->writeHeader();
        //check end-of-file marker
        $stream = $this->getStream();

        if (!empty($this->table->options['create'])) {
            $stream->writeUChar(self::END_OF_FILE_MARKER);
        } else {
            $stat = $stream->stat();
            $stream->seek($stat['size'] - 1);
            if (self::END_OF_FILE_MARKER !== ($lastByte = $stream->readUChar())) {
                $stream->writeUChar(self::END_OF_FILE_MARKER);
            }
        }

        return $this;
    }

    protected function writeHeader(): void
    {
        HeaderWriterFactory::create($this->getHeader()->version, $this->getStream())
            ->write($this->getHeader());
    }
}
