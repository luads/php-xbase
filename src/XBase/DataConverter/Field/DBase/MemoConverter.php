<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase;

use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Memo\MemoObject;
use XBase\DataConverter\Field\AbstractFieldDataConverter;

class MemoConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::MEMO;
    }

    public function fromBinaryString(string $value): ?MemoObject
    {
        if (!TableType::hasMemo($this->table->getVersion())) {
            throw new \LogicException('Table not supports Memo');
        }

        return $this->table->getMemo()->get($value);
    }

    /**
     * @param MemoObject|null $memoObject
     */
    public function toBinaryString($memoObject): string
    {
        if (!$memoObject) {
            return str_pad('', $this->column->getLength(), chr(0x00), STR_PAD_LEFT);
        }

        if ($memoObject->isEdited()) {
            //todo
        }

        return str_pad((string) $memoObject->getPointer(), $this->column->getLength(), ' ', STR_PAD_LEFT);
    }
}
