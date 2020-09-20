<?php declare(strict_types=1);

namespace XBase\Record\DataConverter;

use XBase\Column\ColumnInterface;
use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Exception\InvalidColumnException;
use XBase\Memo\MemoObject;
use XBase\Record\AbstractRecord;
use XBase\Record\RecordInterface;
use XBase\Table;

class DBaseDataConverter implements DataConverterInterface
{
    /** @var Table */
    protected $table;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    /**
     * @return array [deleted, data]
     */
    public function fromBinaryString(string $rawData): array
    {
        $result = [
            'deleted'     => $rawData && (AbstractRecord::FLAG_DELETED === ord($rawData[0])),
            'data'        => [],
            'choppedData' => [],//todo remove in 1.4
        ];

        foreach ($this->table->getColumns() as $column) {
            if ($rawData) {
                $rawValue = substr($rawData, $column->getBytePos(), $column->getLength());
                $normalValue = $this->normalize($column, $rawValue);
            } else {
                $rawValue = $normalValue = null;
            }
            $result['data'][$column->getName()] = $normalValue;
            $result['choppedData'][$column->getName()] = $rawValue; //todo remove in 1.4
        }

        return $result;
    }

    public function toBinaryString(RecordInterface $record): string
    {
        $result = chr($record->isDeleted() ? AbstractRecord::FLAG_DELETED : AbstractRecord::FLAG_NOT_DELETED);
        foreach ($this->table->getColumns() as $column) {
            $result .= $this->denormalize($column, $record);
        }
        return $result;
    }

    /**
     * @return bool|false|float|int|string|null
     *
     * @throws InvalidColumnException If dataType not exists
     */
    protected function normalize(ColumnInterface $column, string $value)
    {
        switch ($column->getType()) {
            case FieldType::CHAR:
                return $this->normalizeString($value);
            case FieldType::DATE:
                return $value;
            case FieldType::LOGICAL:
                return $this->normalizeBoolean($value);
            case FieldType::MEMO:
                return $this->normalizeMemo($value);
            case FieldType::NUMERIC:
                return $this->normalizeNumber($column, $value);
            case FieldType::IGNORE:
                return false;
            case FieldType::BLOB:
                return trim($value);
        }

        throw new InvalidColumnException(sprintf('Cannot handle datatype %s', $column->getType()));
    }

    protected function denormalize(ColumnInterface $column, RecordInterface $record): string
    {
        $value = $record->get($column->getName());

        switch ($column->getType()) {
            case FieldType::CHAR:
                return $this->denormalizeString($column, $value ?? '');
            case FieldType::DATE:
                return $this->denormalizeDate($column, $value);
            case FieldType::LOGICAL:
                return $this->denormalizeBoolean($value);
            case FieldType::MEMO:
                return $this->denormalizeMemo($column, $value);
            case FieldType::NUMERIC:
                return $this->denormalizeNumber($column, $value);
            case FieldType::IGNORE:
                return str_pad('', $column->getLength(), chr(0x00));
            case FieldType::BLOB:
                return trim($value ?? '');
        }

        throw new InvalidColumnException(sprintf('Cannot handle datatype %s', $column->getType()));
    }

    public function denormalizeDate(ColumnInterface $column, ?string $value): string
    {
        return null === $value ? str_pad('', $column->getLength()) : $value;
    }

    private function normalizeBoolean(string $value): bool
    {
        if (!$value) {
            return false;
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

    public function denormalizeBoolean(?bool $value): string
    {
        return $value ? 'T' : 'F';
    }

    private function normalizeMemo(string $pointer): ?MemoObject
    {
        if (!TableType::hasMemo($this->table->getVersion())) {
            throw new \LogicException('Table not supports Memo');
        }

        return $this->table->getMemo()->get($pointer);
    }

    private function denormalizeMemo(ColumnInterface $column, ?MemoObject $memoObject): string
    {
        if (!$memoObject) {
            return str_pad('', $column->getLength(), ' ', STR_PAD_LEFT);
        }

        if ($memoObject->isEdited()) {
            //todo
        }

        return str_pad((string) $memoObject->getPointer(), $column->getLength(), ' ', STR_PAD_LEFT);
    }

    /**
     * @return float|int
     */
    private function normalizeNumber(ColumnInterface $column, string $value)
    {
        $s = trim($value);

        $s = str_replace(',', '.', $s);

        if ($column->getDecimalCount() > 0 || $column->getLength() > 9) {
            return (float) $s;
        }

        return (int) $s;
    }

    private function denormalizeNumber(ColumnInterface $column, $value)
    {
        $value = null === $value ? '' : number_format($value, $column->getDecimalCount(), '.', '');
        return str_pad($value, $column->getLength(), ' ', STR_PAD_LEFT);
    }

    private function normalizeString(string $value): string
    {
        if ($inCharset = $this->table->getConvertFrom()) {
            $value = iconv($inCharset, 'utf-8', $value);
        }

        return trim($value);
    }

    private function denormalizeString(ColumnInterface $column, string $value): string
    {
        if ($outCharset = $this->table->getConvertFrom()) {
            $value = iconv('utf-8', $outCharset, $value);
        }

        return str_pad($value, $column->getLength());
    }
}
