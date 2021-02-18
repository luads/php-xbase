<?php declare(strict_types=1);

namespace XBase\Header\Writer;

use XBase\Header\HeaderInterface;
use XBase\Stream\Stream;

abstract class AbstractHeaderWriter implements HeaderWriterInterface
{
    /** @var static */
    protected $filepath;

    /** @var Stream */
    protected $fp;

    public function __construct(string $filepath)
    {
        $this->filepath = $filepath;
        $this->fp = Stream::createFromFile($filepath);
    }

    public function write(HeaderInterface $header): void
    {

    }
}