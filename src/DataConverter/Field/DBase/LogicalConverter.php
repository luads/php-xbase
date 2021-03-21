<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\DBase;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;

class LogicalConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::LOGICAL;
    }

    public function fromBinaryString(string $value): ?bool
    {
        if (' ' === $value) {
            return null;
        }

        switch (strtoupper($value)) {
            case 'T':
            case 'Y':
            case 'J':
            case '1':
                return true;

            default:
                return false;
        }
    }

    public function toBinaryString($value): string
    {
        if (null === $value) {
            return ' ';
        }

        return $value ? 'T' : 'F';
    }
}
