<?php declare(strict_types=1);

namespace XBase\Header\Column\Validator\DBase7;

use XBase\Enum\FieldType;
use XBase\Header\Column\Validator\DBase\MemoValidator;

class BlobValidator extends MemoValidator
{
    public function getType(): array
    {
        return [
            FieldType::DBASE4_BLOB,
            FieldType::GENERAL,
        ];
    }
}
