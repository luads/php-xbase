<?php declare(strict_types=1);

namespace XBase\Header\Writer;

use XBase\Enum\TableType;
use XBase\Header\HeaderInterface;
use XBase\Header\VisualFoxproHeader;

class VisualFoxproHeaderWriter extends AbstractHeaderWriter
{
    protected function writeRest(HeaderInterface $header): void
    {
        assert($header instanceof VisualFoxproHeader);
        assert(in_array($header->getVersion(), [
            TableType::VISUAL_FOXPRO,
            TableType::VISUAL_FOXPRO_AI,
            TableType::VISUAL_FOXPRO_VAR,
        ]));

        parent::writeRest($header);

        $this->fp->write(str_pad($header->getBacklist(), 263));
    }
}
