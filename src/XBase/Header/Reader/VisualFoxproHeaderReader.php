<?php declare(strict_types=1);

namespace XBase\Header\Reader;

use XBase\Header\VisualFoxproHeader;

class VisualFoxproHeaderReader extends AbstractHeaderReader
{
    /** @var int Visual FoxPro backlist length */
    const VFP_BACKLIST_LENGTH = 263;

    protected function getClass(): string
    {
        return VisualFoxproHeader::class;
    }

    /**
     * @return float|int
     */
    protected function getLogicalFieldCount(int $terminatorLength = 1)
    {
        $headerLength = static::getHeaderLength() + $terminatorLength; // [Terminator](1)
        $fieldLength = static::getFieldLength();
        //backlist
        $extraSize = $this->header->getLength() - ($headerLength + self::VFP_BACKLIST_LENGTH);

        return $extraSize / $fieldLength;
    }

    protected function readRest(): void
    {
        assert($this->header instanceof VisualFoxproHeader);

        $this->header->setBacklist($this->fp->read(self::VFP_BACKLIST_LENGTH));
    }
}
