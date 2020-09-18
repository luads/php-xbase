<?php

namespace XBase;

use XBase\Column\ColumnInterface;
use XBase\Enum\Codepage;
use XBase\Enum\TableType;
use XBase\Exception\TableException;
use XBase\Header\Builder\HeaderBuilderFactory;
use XBase\Header\HeaderInterface;
use XBase\Memo\MemoFactory;
use XBase\Memo\MemoInterface;
use XBase\Record\RecordFactory;
use XBase\Record\RecordInterface;
use XBase\Stream\Stream;

class Table
{
    /** @var HeaderInterface */
    protected $header;

    /** @var string Table filepath. */
    protected $filepath;

    /** @var array|null */
    protected $availableColumns;

    /** @var Stream */
    protected $fp;

    /** @var int Current record position. */
    protected $recordPos = -1;

    /** @var int */
    protected $deleteCount = 0;

    /** @var RecordInterface|null */
    protected $record;

    /** @var string|null */
    protected $convertFrom;

    /**
     * @var MemoInterface|null
     */
    protected $memo;

    /**
     * Table constructor.
     *
     * @param array|null  $availableColumns
     * @param string|null $convertFrom      Encoding of file
     *
     * @throws \Exception
     */
    public function __construct(string $filepath, $availableColumns = null, $convertFrom = null)
    {
        $this->filepath = $filepath;
        $this->availableColumns = $availableColumns;
        $this->convertFrom = $convertFrom; //todo autodetect from languageCode
        $this->open();

        if (TableType::hasMemo($this->getVersion())) {
            $this->memo = MemoFactory::create($this);
        }
    }

    protected function open(): void
    {
        if (!file_exists($this->filepath)) {
            throw new \Exception(sprintf('File %s cannot be found', $this->filepath));
        }

        if ($this->fp) {
            $this->fp->close();
        }

        $this->fp = Stream::createFromFile($this->filepath);
        $this->readHeader();
    }

    public function close(): void
    {
        $this->fp->close();
    }

    protected function readHeader(): void
    {
        $this->header = HeaderBuilderFactory::create($this->filepath)
            ->build()
            ->getHeader();
        $this->fp->seek($this->header->getLength());

        $this->recordPos = -1;
        $this->record = false;
        $this->deleteCount = 0;
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
            if (($this->recordPos + 1) >= $this->header->getRecordCount()) {
                return null;
            }

            $this->recordPos++;
            $this->record = RecordFactory::create($this, $this->recordPos, $this->fp->read($this->header->getRecordByteLength()));

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
        if ($position >= $this->header->getRecordCount()) {
            throw new TableException("Row with index {$position} does not exists");
        }

        $curPos = $this->fp->tell();
        $seekPos = $this->header->getLength() + $position * $this->header->getRecordByteLength();
        if (0 !== $this->fp->seek($seekPos)) {
            throw new TableException("Failed to pick row at position {$position}");
        }

        $record = RecordFactory::create($this, $position, $this->fp->read($this->header->getRecordByteLength()));
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

            $this->fp->seek($this->header->getLength() + ($this->recordPos * $this->header->getRecordByteLength()));

            $this->record = RecordFactory::create($this, $this->recordPos, $this->fp->read($this->getRecordByteLength()));

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

        $this->fp->seek($this->header->getLength() + ($index * $this->header->getRecordByteLength()));

        $this->record = RecordFactory::create($this, $this->recordPos, $this->fp->read($this->header->getRecordByteLength()));

        return $this->record;
    }

    /**
     * @param $name
     *
     * @return ColumnInterface
     */
    public function getColumn($name)
    {
        foreach ($this->header->getColumns() as $column) {
            if ($column->getName() === $name) {
                return $column;
            }
        }

        throw new \Exception(sprintf('Column %s not found', $name));
    }

    /**
     * @return RecordInterface|null
     */
    public function getRecord()
    {
        return $this->record;
    }

    public function getCodepage(): int
    {
        return $this->header->getLanguageCode();
    }

    /**
     * @return ColumnInterface[]
     */
    public function getColumns()
    {
        return $this->header->getColumns();
    }

    /**
     * @return int
     */
    public function getColumnCount()
    {
        return count($this->getColumns());
    }

    /**
     * @return int
     */
    public function getRecordCount()
    {
        return $this->header->getRecordCount();
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
        return $this->header->getRecordByteLength();
    }

    /**
     * @return string
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    public function getVersion()
    {
        return $this->header->getVersion();
    }

    /**
     * @see Codepage
     */
    public function getLanguageCode(): int
    {
        return $this->header->getLanguageCode();
    }

    public function getMemo(): ?MemoInterface
    {
        return $this->memo;
    }

    /**
     * @return int
     */
    public function getDeleteCount()
    {
        return $this->deleteCount;
    }

    /**
     * @return string|null
     */
    public function getConvertFrom()
    {
        return $this->convertFrom;
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
        return $this->header->isFoxpro();
    }

    public function getModifyDate()
    {
        return $this->header->getModifyDate();
    }

    public function isInTransaction(): bool
    {
        return $this->header->isInTransaction();
    }

    public function isEncrypted(): bool
    {
        return $this->header->isEncrypted();
    }

    public function getMdxFlag(): string
    {
        return chr($this->header->getMdxFlag());
    }

    public function getHeaderLength(): int
    {
        return $this->header->getLength();
    }
}
