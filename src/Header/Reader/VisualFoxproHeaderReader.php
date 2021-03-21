<?php declare(strict_types=1);

namespace XBase\Header\Reader;

use XBase\Enum\TableType;
use XBase\Header\Specification\HeaderSpecificationFactory;

class VisualFoxproHeaderReader extends AbstractHeaderReader
{
    /** @var int Visual FoxPro backlist length */
    const VFP_BACKLIST_LENGTH = 263;

    /**
     * @return float|int
     */
    protected function getLogicalFieldCount(int $terminatorLength = 1)
    {
        $spec = HeaderSpecificationFactory::create($this->header->version);

        $headerLength = $spec->headerTopLength + $terminatorLength; // [Terminator](1)
        //backlist
        $extraSize = $this->header->length - ($headerLength + self::VFP_BACKLIST_LENGTH);

        return $extraSize / $spec->fieldLength;
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
