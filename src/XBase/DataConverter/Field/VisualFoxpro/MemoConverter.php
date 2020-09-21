<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\VisualFoxpro;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;
use XBase\Memo\MemoObject;

class MemoConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::MEMO;
    }

    public function fromBinaryString(string $value): ?MemoObject
    {
        $pointer = unpack('l', $value)[1];
        return $this->table->getMemo()->get($pointer);
    }

    /**
     * @param MemoObject|null $value
     */
    public function toBinaryString($value): string
    {
        if ($value instanceof MemoObject) {
            $value = $value->getPointer();
        }

        return pack('l', $value);
    }
}
