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

    public function fromBinaryString(string $value): ?int
    {
        if (!TableType::hasMemo($this->table->getVersion())) {
            throw new \LogicException('Table not supports Memo');
        }

        if (empty($pointer = ltrim($value, ' '))) {
            return null;
        }

        return (int) $pointer;
    }

    /**
     * @param int|null $value
     */
    public function toBinaryString($value): string
    {
        if (!$value) {
            return str_pad('', $this->column->getLength(), chr(0x00), STR_PAD_LEFT);
        }

        return str_pad((string) $value, $this->column->getLength(), ' ', STR_PAD_LEFT);
    }
}
