<?php declare(strict_types=1);

namespace XBase\DataConverter\Record;

use XBase\Column\ColumnInterface;
use XBase\DataConverter\Field\DBase\DateConverter;
use XBase\DataConverter\Field\DBase\IgnoreConverter;
use XBase\DataConverter\Field\DBase\LogicalConverter;
use XBase\DataConverter\Field\DBase\MemoConverter;
use XBase\DataConverter\Field\DBase\NumberConverter;
use XBase\DataConverter\Field\DBase\StringConverter;
use XBase\DataConverter\Field\FieldDataConverterInterface;
use XBase\Exception\InvalidColumnException;
use XBase\Record\AbstractRecord;
use XBase\Record\RecordInterface;
use XBase\Table;

class DBaseDataConverter implements RecordDataConverterInterface
{
    /** @var Table */
    protected $table;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    /**
     * @return FieldDataConverterInterface[]
     */
    protected static function getFiledConverters(): array
    {
        return [
            DateConverter::class,
            IgnoreConverter::class,
            LogicalConverter::class,
            MemoConverter::class,
            NumberConverter::class,
            StringConverter::class,
        ];
    }

    /**
     * @return array [deleted, data]
     */
    public function fromBinaryString(string $rawData): array
    {
        $result = [
            'deleted'     => $rawData && (AbstractRecord::FLAG_DELETED === ord($rawData[0])),
            'data'        => [],
            'choppedData' => [], //todo remove in 1.4
        ];

        foreach ($this->table->getColumns() as $column) {
            if ($rawData) {
                $rawValue = substr($rawData, $column->getBytePos(), $column->getLength());
                $normalValue = $this->normalizeField($column, $rawValue);
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
            $result .= $this->denormalizeField($column, $record);
        }

        if (($act = strlen($result)) !== ($len = $this->table->getRecordByteLength())) {
            throw new \LogicException(sprintf('Invalid number of bytes in binary string. Expected: %d. Actual: %d', $len, $act));
        }

        return $result;
    }

    private function findFieldConverter(ColumnInterface $column): FieldDataConverterInterface
    {
        foreach (static::getFiledConverters() as $class) {
            if ($column->getType() === $class::getType()) {
                return new $class($this->table, $column);
            }
        }

        throw new InvalidColumnException(sprintf('Cannot find Field for `%s` data type', $column->getType()));
    }

    /**
     * @return bool|false|float|int|string|null
     *
     * @throws InvalidColumnException If dataType not exists
     */
    protected function normalizeField(ColumnInterface $column, string $value)
    {
        return $this->findFieldConverter($column)->fromBinaryString($value);
    }

    protected function denormalizeField(ColumnInterface $column, RecordInterface $record): string
    {
        $value = $record->getGenuine($column->getName()); //todo memo get raw value

        return $this->findFieldConverter($column)->toBinaryString($value);
    }
}
