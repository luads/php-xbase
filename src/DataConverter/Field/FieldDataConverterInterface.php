<?php declare(strict_types=1);

namespace XBase\DataConverter\Field;

interface FieldDataConverterInterface
{
    public static function getType(): string;

    /**
     * Convert database binary string to normal field value.
     */
    public function fromBinaryString(string $value);

    /**
     * Convert normal field value to binary string.
     */
    public function toBinaryString($value): string;
}
