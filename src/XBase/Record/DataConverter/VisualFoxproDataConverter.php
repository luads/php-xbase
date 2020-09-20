<?php declare(strict_types=1);

namespace XBase\Record\DataConverter;

use XBase\Column\ColumnInterface;
use XBase\Enum\FieldType;
use XBase\Record\RecordInterface;

class VisualFoxproDataConverter extends FoxproDataConverter
{
    const ZERO_DATE = 0x253d8c;

    protected function normalize(ColumnInterface $column, string $value)
    {
        switch ($column->getType()) {
            case FieldType::CURRENCY:
                return $this->normalizeCurrency($value);
            case FieldType::DOUBLE:
                return $this->normalizeDouble($value);
            case FieldType::DATETIME:
                return $this->normalizeDateTime($value);
            case FieldType::GENERAL:
                return $this->normalizeGeneral($value);
            case FieldType::FLOAT:
                return $this->normalizeFloat($value);
            case FieldType::INTEGER:
                return $this->normalizeInt($column, $value);
            case FieldType::VAR_FIELD:
                return $this->normalizeVar($value);
            case FieldType::VARBINARY:
                return $this->normalizeVar($value, true);
            default:
                return parent::normalize($column, $value);
        }
    }

    protected function denormalize(ColumnInterface $column, RecordInterface $record): string
    {
        $value = $record->get($column->getName());

        switch ($column->getType()) {
            case FieldType::CURRENCY:
                return $this->denormalizeCurrency($column, $value);
            case FieldType::DOUBLE:
                return $this->denormalizeDouble($column, $value);
            case FieldType::DATETIME:
                return $this->denormalizeDateTime($value);
            case FieldType::GENERAL:
                return $this->denormalizeGeneral($value);
            case FieldType::FLOAT:
                return $this->denormalizeFloat($value);
            case FieldType::INTEGER:
                return $this->denormalizeInt($column, $value);
            case FieldType::VAR_FIELD:
                return $this->denormalizeVar($value);
            case FieldType::VARBINARY:
                return $this->denormalizeVar($value, true);
            default:
                return parent::denormalize($column, $record);
        }
    }

    private function normalizeInt(ColumnInterface $column, string $value): ?int
    {
        if (!$value) {
            return null;
        }

        if ($this->table->isFoxpro()) {
            $su = unpack('i', $value);
            $ret = $su[1];
        } else {
            $ret = ord($value[0]);

            $length = $column->getLength();

            for ($i = 1; $i < $length; $i++) {
                $ret += $i * 256 * ord($value[$i]);
            }
        }

        return $ret;
    }

    private function normalizeDouble(string $value): float
    {
        $value = unpack('d', $value);

        if ($value) {
            return (float) $value[1];
        }

        return 0.0;
    }

    private function normalizeDateTime(string $value): ?int
    {
        $buf = unpack('i*', $value);
        $intDate = $buf[1];
        $intTime = $buf[2];

        if (0 == $intDate && 0 == $intTime) {
            return null;
        }

        $longDate = ($intDate - self::ZERO_DATE) * 86400;

        return $longDate + ($intTime / 1000);
    }

    private function normalizeCurrency(string $value): float
    {
        $value = unpack('q', $value);

        if ($value) {
            return $value[1] / 10000;
        }

        return 0.0;
    }

    private function normalizeFloat(string $value): float
    {
        return (float) ltrim($value);
    }

    public function normalizeVar(string $value, bool $isBinary = false): string
    {
        if (false !== ($pos = strpos($value, chr(0x00)))) {
            $value = substr($value, 0, $pos);
        }

        if (false === $isBinary && $inCharset = $this->table->getConvertFrom()) {
            $value = iconv($inCharset, 'utf-8', $value);
        }

        return $value;
    }

    private function normalizeGeneral(string $value): int
    {
        $data = unpack('L', $value);
        return $data[1];
    }

    private function denormalizeCurrency(ColumnInterface $column, ?float $value): string
    {
        if (null === $value) {
            return str_pad('', $column->getLength(), chr(0x00));
        }

        return pack('q', (int) ($value * 10000));
    }

    private function denormalizeDouble(ColumnInterface $column, ?float $value)
    {
        if (null === $value) {
            return str_pad('', $column->getLength(), chr(0x00));
        }

        return pack('d', $value);
    }

    private function denormalizeDateTime($value): string
    {
        return '';//todo
    }

    private function denormalizeGeneral($value): string
    {
        return '';//todo
    }

    private function denormalizeFloat($value): string
    {
        return '';//todo
    }

    private function denormalizeInt($value): string
    {
        return '';//todo
    }

    private function denormalizeVar($value, bool $binary = false): string
    {
        return '';//todo
    }
}
