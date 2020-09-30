<?php declare(strict_types=1);

namespace XBase\DataConverter\Field\VisualFoxpro;

use XBase\DataConverter\Field\AbstractFieldDataConverter;
use XBase\Enum\FieldType;

class VarFieldConverter extends AbstractFieldDataConverter
{
    public static function getType(): string
    {
        return FieldType::VAR_FIELD;
    }

    public function fromBinaryString(string $value): string
    {
        if (false !== ($pos = strpos($value, chr(0x00)))) {
            $value = substr($value, 0, $pos);
        }

        if ($inCharset = $this->table->getConvertFrom()) {
            $value = iconv($inCharset, 'utf-8', $value);
        }

        return $value;
    }

    /**
     * @param string|null $value
     */
    public function toBinaryString($value): string
    {
        $value = $value ?? '';
        if ($outCharset = $this->table->getConvertFrom()) {
            $value = iconv('utf-8', $outCharset, $value);
        }

        return str_pad($value, $this->column->getLength() - 1, chr(0x00)).chr(0x03);
    }
}
