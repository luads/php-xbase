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

        if ($inCharset = $this->table->options['encoding']) {
            $value = $this->encoder->encode($value, $inCharset, 'utf-8');
        }

        return $value;
    }

    /**
     * @param string|null $value
     */
    public function toBinaryString($value): string
    {
        $value = $value ?? '';
        if ($outCharset = $this->table->options['encoding']) {
            $value = $this->encoder->encode($value, 'utf-8', $outCharset);
        }

        return str_pad($value, $this->column->length - 1, chr(0x00)).chr(0x03);
    }
}
