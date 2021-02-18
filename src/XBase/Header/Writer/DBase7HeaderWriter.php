<?php declare(strict_types=1);

namespace XBase\Header\Writer;

use XBase\Enum\TableType;
use XBase\Header\DBase7Header;
use XBase\Header\HeaderInterface;

class DBase7HeaderWriter extends AbstractHeaderWriter
{
    protected function writeFirstBlock(HeaderInterface $header): void
    {
        assert($header instanceof DBase7Header);

        parent::writeFirstBlock($header);

        if (in_array($header->getVersion(), [TableType::DBASE_7_MEMO, TableType::DBASE_7_NOMEMO])) {
            $this->fp->write(str_pad($header->getLanguageName(), 36, chr(0)));
        }
    }
}
