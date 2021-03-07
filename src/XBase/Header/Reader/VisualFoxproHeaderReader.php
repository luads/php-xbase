<?php declare(strict_types=1);

namespace XBase\Header\Reader;

use XBase\Enum\TableType;

class VisualFoxproHeaderReader extends AbstractHeaderReader
{
    /** @var int Visual FoxPro backlist length */
    const VFP_BACKLIST_LENGTH = 263;

    /**
     * @return float|int
     */
    protected function getLogicalFieldCount(int $terminatorLength = 1)
    {
        $headerLength = static::getHeaderLength() + $terminatorLength; // [Terminator](1)
        $fieldLength = static::getFieldLength();
        //backlist
        $extraSize = $this->header->length - ($headerLength + self::VFP_BACKLIST_LENGTH);

        return $extraSize / $fieldLength;
    }

    protected function readRest(): void
    {
        assert(in_array($this->header->version, [
            TableType::VISUAL_FOXPRO,
            TableType::VISUAL_FOXPRO_AI,
            TableType::VISUAL_FOXPRO_VAR,
        ]));

        $this->header->backlist = $this->fp->read(self::VFP_BACKLIST_LENGTH);
    }
}
