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

        parent::writeRest($header);

        if (in_array($header->getVersion(), [
            TableType::VISUAL_FOXPRO,
            TableType::VISUAL_FOXPRO_AI,
            TableType::VISUAL_FOXPRO_VAR,
        ])) {
            $this->fp->write(str_pad($header->getBacklist(), 263));
        }
    }
}
