<?php declare(strict_types=1);

namespace XBase\DataConverter\Record;

use XBase\DataConverter\Encoder\EncoderInterface;
use XBase\DataConverter\Field\DBase\CharConverter;
use XBase\DataConverter\Field\DBase\DateConverter;
use XBase\DataConverter\Field\DBase\IgnoreConverter;
use XBase\DataConverter\Field\DBase\LogicalConverter;
use XBase\DataConverter\Field\DBase\MemoConverter;
use XBase\DataConverter\Field\DBase\NumberConverter;
use XBase\DataConverter\Field\FieldDataConverterInterface;
use XBase\Exception\InvalidColumnException;
use XBase\Header\Column;
use XBase\Record\AbstractRecord;
use XBase\Record\RecordInterface;
use XBase\Table\Table;

class DBaseDataConverter implements RecordDataConverterInterface
{
    /** @var Table */
    protected $table;

    /** @var EncoderInterface */
    protected $encoder;

    public function __construct(Table $table, EncoderInterface $encoder)
    {
        $this->table = $table;
        $this->encoder = $encoder;
    }

    /**
     * @return FieldDataConverterInterface[]
     */
    protected static function getFieldConverters(): array
    {
        return [
            DateConverter::class,
            IgnoreConverter::class,
            LogicalConverter::class,
            MemoConverter::class,
            NumberConverter::class,
            CharConverter::class,
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
        ];

        foreach ($this->table->header->columns as $column) {
            $normalValue = null;
            if ($rawData) {
                $rawValue = substr($rawData, $column->bytePosition, $column->length);
                $normalValue = $this->normalizeField($column, $rawValue);
            }
            $result['data'][$column->name] = $normalValue;
        }

        return $result;
    }

    public function toBinaryString(RecordInterface $record): string
    {
        $result = chr($record->isDeleted() ? AbstractRecord::FLAG_DELETED : AbstractRecord::FLAG_NOT_DELETED);
        foreach ($this->table->header->columns as $column) {
            $result .= $this->denormalizeField($column, $record);
        }

        if (($act = strlen($result)) !== ($len = $this->table->header->recordByteLength)) {
            throw new \LogicException(sprintf('Invalid number of bytes in binary string. Expected: %d. Actual: %d', $len, $act));
        }

        return $result;
    }

    private function findFieldConverter(Column $column): FieldDataConverterInterface
    {
        foreach (static::getFieldConverters() as $class) {
            if ($column->type === $class::getType()) {
                return new $class($this->table, $column, $this->encoder);
            }
        }

        throw new InvalidColumnException(sprintf('Cannot find Field for `%s` data type', $column->type));
    }

    /**
     * @return bool|false|float|int|string|null
     *
     * @throws InvalidColumnException If dataType not exists
     */
    protected function normalizeField(Column $column, string $value)
    {
        return $this->findFieldConverter($column)->fromBinaryString($value);
    }

    protected function denormalizeField(Column $column, RecordInterface $record): string
    {
        $value = $record->getGenuine($column->name); //todo memo get raw value

        return $this->findFieldConverter($column)->toBinaryString($value);
    }
}
