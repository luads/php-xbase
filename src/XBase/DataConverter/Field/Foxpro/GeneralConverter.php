<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\Foxpro;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;
use XBase\Memo\MemoObject;

class GeneralConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::GENERAL;
    }

    public function fromBinaryString(string $value): ?MemoObject
    {
        return $this->table->getMemo()->get($value);
    }

    public function toBinaryString($value): string
    {
        //todo
        throw new \Exception('NotRealized');
    }
}
