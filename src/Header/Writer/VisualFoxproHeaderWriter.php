<?php declare(strict_types=1);

namespace XBase\Header\Writer;

use XBase\Enum\TableType;
use XBase\Header\Header;

class VisualFoxproHeaderWriter extends AbstractHeaderWriter
{
    protected function writeRest(Header $header): void
    {
        assert(in_array($header->version, [
            TableType::VISUAL_FOXPRO,
            TableType::VISUAL_FOXPRO_AI,
            TableType::VISUAL_FOXPRO_VAR,
        ]));

        parent::writeRest($header);

        $this->fp->write(str_pad($header->backlist, 263));
    }
}
