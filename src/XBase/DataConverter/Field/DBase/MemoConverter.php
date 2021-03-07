<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;
use XBase\Enum\TableType;

class MemoConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::MEMO;
    }

    protected function getFillerChar(): string
    {
        return ' ';
    }

    public function fromBinaryString(string $value): ?int
    {
        if (!TableType::hasMemo($this->table->getVersion())) {
            throw new \LogicException('Table not supports Memo');
        }

        if (empty($pointer = ltrim($value, $this->getFillerChar()))) {
            return null;
        }

        return (int) $pointer;
    }

    /**
     * @param int|null $value
     */
    public function toBinaryString($value): string
    {
        if (null === $value) {
            return str_repeat(chr(0x00), $this->column->length);
        }

        return str_pad((string) $value, $this->column->length, $this->getFillerChar(), STR_PAD_LEFT);
    }
}
