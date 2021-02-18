<?php declare(strict_types=1);

namespace XBase\Header\Reader;

use XBase\Column\DBase7Column;
use XBase\Enum\TableType;
use XBase\Header\VisualFoxproHeader;

class VisualFoxproReader extends AbstractHeaderReader
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
        $headerLength = self::HEADER_LENGTH + $terminatorLength; // [Terminator](1)
        $fieldLength = self::FIELD_LENGTH;
        if (in_array($this->header->getVersion(), [TableType::DBASE_7_MEMO, TableType::DBASE_7_NOMEMO])) {
            $headerLength += 36; // [Language driver name](32) + [Reserved](4) +
            $fieldLength = DBase7Column::getHeaderLength();
        }
        //backlist
        $extraSize = $this->header->getLength() - ($headerLength + self::VFP_BACKLIST_LENGTH);

        return $extraSize / $fieldLength;
    }

    protected function readRest(): void
    {
        $this->header->setBacklist($this->fp->read(self::VFP_BACKLIST_LENGTH));
    }
}
