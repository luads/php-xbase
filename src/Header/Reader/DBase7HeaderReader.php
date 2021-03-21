<?php declare(strict_types=1);

namespace XBase\Header\Reader;

use XBase\Header\Specification\HeaderSpecificationFactory;

class DBase7HeaderReader extends AbstractHeaderReader
{
    protected function extractArgs(): array
    {
        $args = parent::extractArgs();
        $args['languageName'] = rtrim($this->fp->read(32), chr(0));
        $this->fp->read(4);

        return $args;
    }

    /**
     * @return float|int
     */
    protected function getLogicalFieldCount(int $terminatorLength = 1)
    {
        $spec = HeaderSpecificationFactory::create($this->header->version);

        $headerLength = $spec->headerTopLength + $terminatorLength; // [Terminator](1)
        $extraSize = $this->header->length - $headerLength;

        return $extraSize / $spec->fieldLength;
    }
}
