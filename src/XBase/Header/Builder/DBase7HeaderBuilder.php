<?php declare(strict_types=1);

namespace XBase\Header\Builder;

use XBase\Column\DBase7Column;
use XBase\Header\DBase7Header;

class DBase7HeaderBuilder extends AbstractHeaderBuilder
{
    protected function getClass(): string
    {
        return DBase7Header::class;
    }

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
        $headerLength = self::HEADER_LENGTH + $terminatorLength; // [Terminator](1)
        $headerLength += 36; // [Language driver name](32) + [Reserved](4) +
        $fieldLength = DBase7Column::getHeaderLength();
        $extraSize = $this->header->getLength() - $headerLength;

        return $extraSize / $fieldLength;
    }
}
