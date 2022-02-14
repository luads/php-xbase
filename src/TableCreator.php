<?php declare(strict_types=1);

namespace XBase;

use XBase\DataConverter\Encoder\EncoderInterface;
use XBase\DataConverter\Encoder\IconvEncoder;
use XBase\Enum\TableType;
use XBase\Exception\ColumnException;
use XBase\Exception\XBaseException;
use XBase\Header\Column;
use XBase\Header\Column\Validator\ColumnValidatorFactory;
use XBase\Header\Header;
use XBase\Header\Specification\HeaderSpecificationFactory;
use XBase\Memo\Creator\MemoCreatorFactory;
use XBase\Memo\MemoFactory;
use XBase\Stream\Stream;
use XBase\Table\Saver;
use XBase\Table\Table;
use XBase\Table\TableAwareTrait;

/**
 * Creates brand-new database file
 */
class TableCreator
{
    use TableAwareTrait;

    /** @var EncoderInterface */
    protected $encoder;

    public function __construct(string $filepath, Header $header, EncoderInterface $encoder = null)
    {
        $this->checkFilepath($filepath);
        $this->checkHeader($header);

        $this->table = new Table();
        $this->table->filepath = $filepath;
        $this->table->header = $header;
        $this->table->options['create'] = true;
        $this->encoder = $encoder ?? new IconvEncoder();
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
            // just creates memo file with no data
            MemoCreatorFactory::create($this->table)->createFile();
            $this->table->memo = MemoFactory::create($this->table, $this->encoder);
        }

        $this->table->stream->close();
        $this->table->stream = null;

        return $this;
    }

    private function prepareHeader(): void
    {
        $header = $this->getHeader();

        $spec = HeaderSpecificationFactory::create($header->version);

        $this->validateColumns($header->columns);
        $header->length = $spec->headerTopLength + count($header->columns) * $spec->fieldLength + 1;

        $header->recordByteLength = 1; //deleted mark
        foreach ($header->columns as $column) {
            assert($column->length);
            $column->memAddress = $header->recordByteLength;
            $header->recordByteLength += $column->length;
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

        $columnValidator = ColumnValidatorFactory::create($this->table->getVersion());

        foreach ($columns as $column) {
            $columnValidator->validate($column);
        }
    }
}
