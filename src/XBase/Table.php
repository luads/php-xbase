<?php declare(strict_types=1);

namespace XBase;

use XBase\Column\ColumnInterface;
use XBase\Column\XBaseColumn;
use XBase\Enum\Codepage;
use XBase\Enum\TableType;
use XBase\Exception\TableException;
use XBase\Header\Header;
use XBase\Header\Reader\HeaderReaderFactory;
use XBase\Memo\MemoFactory;
use XBase\Memo\MemoInterface;
use XBase\Record\RecordFactory;
use XBase\Record\RecordInterface;
use XBase\Stream\Stream;
use XBase\Table\Table as TableStruct;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
class Table
{
    const END_OF_FILE_MARKER = 0x1a;

    /** @var Stream */
    protected $fp;

    /** @var int Current record position. */
    protected $recordPos = -1;

    /** @var int */
    protected $deleteCount = 0;

    /** @var RecordInterface|null */
    protected $record;

    /**
     * @var TableStruct
     */
    protected $table;

    /**
     * Table constructor.
     *
     * @param array $options Array of options:<br>
     *                       encoding - convert text data from<br>
     *                       columns - available columns<br>
     *
     * @throws \Exception
     */
    public function __construct(string $filepath, array $options = [])
    {
        $this->table = new TableStruct();
        $this->table->filepath = $filepath;

        $this->table->options = $this->resolveOptions($options);

        $this->open();
        $this->readHeader();
        $this->openMemo();
    }

    protected function resolveOptions(array $options): array
    {
        return array_merge([
            'columns'  => [],
            'encoding' => null,
            'editMode' => null,
        ], $options);
    }

    protected function open(): void
    {
        if (!file_exists($this->getFilepath())) {
            throw new \Exception(sprintf('File %s cannot be found', $this->getFilepath()));
        }

        if ($this->fp) {
            $this->fp->close();
        }

        $this->fp = Stream::createFromFile($this->getFilepath());
    }

    protected function readHeader(): void
    {
        $this->table->header = HeaderReaderFactory::create($this->getFilepath())->read();
        $this->fp->seek($this->table->header->length);

        $this->recordPos = -1;
        $this->deleteCount = 0;
    }

    protected function openMemo(): void
    {
        if (TableType::hasMemo($this->getVersion())) {
            $this->table->memo = MemoFactory::create($this->table);
        }
    }

    protected function getHeader(): Header
    {
        return $this->table->header;
    }

    protected function getMemo(): ?MemoInterface
    {
        return $this->table->memo;
    }

    public function close(): void
    {
        $this->fp->close();
        if ($memo = $this->getMemo()) {
            $memo->close();
        }
    }

    public function nextRecord(): ?RecordInterface
    {
        if (!$this->isOpen()) {
            $this->open();
        }

        if ($this->record) {
            $this->record->destroy();
            $this->record = null;
        }

        $valid = false;

        do {
            if (($this->recordPos + 1) >= $this->getHeader()->recordCount) {
                return null;
            }

            $this->recordPos++;
            $this->record = RecordFactory::create($this->table, $this->recordPos, $this->fp->read($this->getHeader()->recordByteLength));

            if ($this->record->isDeleted()) {
                $this->deleteCount++;
            } else {
                $valid = true;
            }
        } while (!$valid);

        return $this->record;
    }

    /**
     * Get record by row index.
     *
     * @param int $position Zero based position
     */
    public function pickRecord(int $position): ?RecordInterface
    {
        if ($position >= $this->getHeader()->recordCount) {
            throw new TableException("Row with index {$position} does not exists");
        }

        $curPos = $this->fp->tell();
        $seekPos = $this->getHeader()->length + $position * $this->getHeader()->recordByteLength;
        if (0 !== $this->fp->seek($seekPos)) {
            throw new TableException("Failed to pick row at position {$position}");
        }

        $record = RecordFactory::create($this->table, $position, $this->fp->read($this->getHeader()->recordByteLength));
        // revert pointer
        $this->fp->seek($curPos);

        return $record;
    }

    public function previousRecord(): ?RecordInterface
    {
        if (!$this->isOpen()) {
            $this->open();
        }

        if ($this->record) {
            $this->record->destroy();
            $this->record = null;
        }

        $valid = false;

        do {
            if (($this->recordPos - 1) < 0) {
                return null;
            }

            $this->recordPos--;

            $this->fp->seek($this->getHeader()->length + ($this->recordPos * $this->getHeader()->recordByteLength));

            $this->record = RecordFactory::create($this->table, $this->recordPos, $this->fp->read($this->getRecordByteLength()));

            if ($this->record->isDeleted()) {
                $this->deleteCount++;
            } else {
                $valid = true;
            }
        } while (!$valid);

        return $this->record;
    }

    public function moveTo(int $index): ?RecordInterface
    {
        $this->recordPos = $index;

        if ($index < 0) {
            return null;
        }

        $this->fp->seek($this->getHeader()->length + ($index * $this->getHeader()->recordByteLength));

        $this->record = RecordFactory::create($this->table, $this->recordPos, $this->fp->read($this->getHeader()->recordByteLength));

        return $this->record;
    }

    /**
     * @param string $name
     *
     * @return ColumnInterface
     */
    public function getColumn($name)
    {
        foreach ($this->getHeader()->columns as $column) {
            if ($column->name === $name) {
                return new XBaseColumn($column);
            }
        }

        throw new \Exception("Column $name not found");
    }

    public function getRecord(): ?RecordInterface
    {
        return $this->record;
    }

    public function getCodepage(): int
    {
        return $this->getHeader()->languageCode;
    }

    /**
     * @return ColumnInterface[]
     */
    public function getColumns(): array
    {
        $columns = [];
        foreach ($this->getHeader()->columns as $column) {
            assert(!empty($column->name));
            $columns[$column->name] = new XBaseColumn($column);
        }

        return $columns;
    }

    public function getColumnCount(): int
    {
        return count($this->getHeader()->columns);
    }

    /**
     * @return int
     */
    public function getRecordCount()
    {
        return $this->getHeader()->recordCount;
    }

    /**
     * @return int
     */
    public function getRecordPos()
    {
        return $this->recordPos;
    }

    public function getRecordByteLength()
    {
        return $this->getHeader()->recordByteLength;
    }

    /**
     * @return string
     */
    public function getFilepath()
    {
        return $this->table->filepath;
    }

    public function getVersion(): int
    {
        return $this->getHeader()->version;
    }

    /**
     * @see Codepage
     */
    public function getLanguageCode(): int
    {
        return $this->getHeader()->languageCode;
    }

    /**
     * @return int
     */
    public function getDeleteCount()
    {
        return $this->deleteCount;
    }

    public function getConvertFrom(): ?string
    {
        return $this->table->options['encoding'];
    }

    /**
     * @return bool
     */
    protected function isOpen()
    {
        return $this->fp ? true : false;
    }

    public function isFoxpro(): bool
    {
        return TableType::isFoxpro($this->getHeader()->version);
    }

    public function getModifyDate()
    {
        return $this->getHeader()->modifyDate;
    }

    public function isInTransaction(): bool
    {
        return $this->getHeader()->inTransaction;
    }

    public function isEncrypted(): bool
    {
        return $this->getHeader()->encrypted;
    }

    public function getMdxFlag(): string
    {
        return chr($this->getHeader()->mdxFlag);
    }

    public function getHeaderLength(): int
    {
        return $this->getHeader()->length;
    }
}
