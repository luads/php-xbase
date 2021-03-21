<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\VisualFoxpro;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;

class VarBinaryConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::VARBINARY;
    }

    public function fromBinaryString(string $value): string
    {
        if (false !== ($pos = strpos($value, chr(0x00)))) {
            $value = substr($value, 0, $pos);
        }

        return $value;
    }

    /**
     * @param string|null $value
     */
    public function toBinaryString($value): string
    {
        return str_pad($value ?? '', $this->column->length - 1, chr(0x00)).chr(0x03);
    }
}
