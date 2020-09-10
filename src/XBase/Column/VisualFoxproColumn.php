<?php declare(strict_types=1);

namespace XBase\Column;

use XBase\Enum\FieldType;

class VisualFoxproColumn extends DBaseColumn
{
    public function getDataLength()
    {
        switch ($this->type) {
            case FieldType::BLOB:
            case FieldType::MEMO:
                return 4;
            default:
                return parent::getDataLength();
        }
    }
}
