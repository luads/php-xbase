<?php declare(strict_types=1);

namespace XBase;

use XBase\DataConverter\Record\HasFieldConvertersInterface;
use XBase\Enum\TableType;
use XBase\Exception\ColumnException;
use XBase\Exception\XBaseException;
use XBase\Header\Column;
use XBase\Header\Header;
use XBase\Memo\Creator\MemoCreatorFactory;
use XBase\Memo\MemoFactory;
use XBase\Record\RecordFactory;
use XBase\Stream\Stream;
use XBase\Table\Saver;
use XBase\Table\Table;
use XBase\Table\TableAwareTrait;

class TableCreator
{
    use TableAwareTrait;

    public function __construct(string $filepath, Header $header)
    {
        $this->checkFilepath($filepath);
        $this->checkHeader($header);

        $this->table = new Table();
        $this->table->filepath = $filepath;
        $this->table->header = $header;
        $this->table->options['create'] = true;
    }

    private function checkFilepath(string $filepath): void
    {
        if (file_exists($filepath)) {
            throw new \LogicException('File already exists: '.$filepath);
        }
    }

    private function checkHeader(Header $header)
    {
        if (empty($header->version)) {
            throw new \LogicException('Header version not specified');
        }
    }

    public function addColumn(Column $column): self
    {
        if (empty($column->rawName) && empty($column->name)) {
            throw new ColumnException('Neither name nor rawName is defined');
        }
        if (empty($column->type)) {
            throw new ColumnException('Type is not defined');
        }

        $this->getHeader()->columns[] = $column;

        return $this;
    }

    public function save(): self
    {
        $this->table->stream = Stream::createFromFile($this->table->filepath, 'wb');

        $this->prepareHeader();

        $saver = new Saver($this->table);
        $saver->save();

        if (TableType::hasMemo($version = $this->getHeader()->version)) {
            MemoCreatorFactory::create($this->table)->createFile();
            $this->table->memo = MemoFactory::create($this->table);
        }

        $this->table->stream->close();
        $this->table->stream = null;

        return $this;
    }

    private function prepareHeader(): void
    {
        $headerTopLength = 32; //todo HeaderSpecification
        $fieldLength = 32; //todo HeaderSpecification

        $header = $this->getHeader();
        $this->validateColumns($header->columns);
        $header->length = $headerTopLength + count($header->columns) * $fieldLength + 1;

        $header->recordByteLength = 1; //deleted mark
        foreach ($header->columns as $column) {
            assert($column->length);
            $header->recordByteLength += $column->length;
            $column->memAddress = $header->recordByteLength;
        }
    }

    /**
     * @param Column[] $columns
     */
    private function validateColumns(array $columns): void
    {
        if (empty($columns)) {
            throw new XBaseException('The table must contain at least one column');
        }

        $dataConverter = RecordFactory::createDataConverter($this->table);
        assert($dataConverter instanceof HasFieldConvertersInterface);
        // todo too heavy. better use collections of supported types
        $supportedTypes = array_map(static function ($fqcn): string {
            return $fqcn::getType();
        }, $dataConverter->getFieldConverters());

        foreach ($columns as $column) {
            if (!in_array($column->type, $supportedTypes)) {
                throw new ColumnException("Table not supports `{$column->type}` column type");
            }
            //todo strict properties
            //todo validate
        }
    }
}
