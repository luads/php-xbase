<?php declare(strict_types=1);

namespace XBase;

use XBase\Column\ColumnInterface;
use XBase\Enum\Codepage;
use XBase\Enum\TableType;
use XBase\Exception\TableException;
use XBase\Header\HeaderInterface;
use XBase\Header\Reader\HeaderReaderFactory;
use XBase\Memo\MemoFactory;
use XBase\Memo\MemoInterface;
use XBase\Record\RecordFactory;
use XBase\Record\RecordInterface;
use XBase\Stream\Stream;

class Table
{
    /** @var HeaderInterface */
    protected $header;

    const END_OF_FILE_MARKER = 0x1a;

    /** @var string Table filepath. */
    protected $filepath;

    /** @var Stream */
    protected $fp;

    /** @var int Current record position. */
    protected $recordPos = -1;

    /** @var int */
    protected $deleteCount = 0;

    /** @var RecordInterface|null */
    protected $record;

    /**
     * @var MemoInterface|null
     */
    protected $memo;

    /** @var array */
    protected $options = [];

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
        $this->filepath = $filepath;
        $this->options = $this->resolveOptions($options);

        $this->open();
        $this->readHeader();
        $this->openMemo();
    }

    protected function resolveOptions(array $options): array
    {
        return array_merge([
            'columns'  => [],
            'encoding' => null,
        ], $options);
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
    }

    protected function readHeader(): void
    {
        $this->header = HeaderReaderFactory::create($this->filepath)->read();
        $this->fp->seek($this->header->length);

        $this->recordPos = -1;
        $this->deleteCount = 0;
    }

    protected function openMemo(): void
    {
        if (TableType::hasMemo($this->getVersion())) {
            $this->memo = MemoFactory::create($this, $this->options);
        }
    }

    public function close(): void
    {
        $this->fp->close();
        if ($this->memo) {
            $this->memo->close();
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
            if (($this->recordPos + 1) >= $this->header->recordCount) {
                return null;
            }

            $this->recordPos++;
            $this->record = RecordFactory::create($this, $this->recordPos, $this->fp->read($this->header->recordByteLength));

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
        if ($position >= $this->header->recordCount) {
            throw new TableException("Row with index {$position} does not exists");
        }

        $curPos = $this->fp->tell();
        $seekPos = $this->header->length + $position * $this->header->recordByteLength;
        if (0 !== $this->fp->seek($seekPos)) {
            throw new TableException("Failed to pick row at position {$position}");
        }

        $record = RecordFactory::create($this, $position, $this->fp->read($this->header->recordByteLength));
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

            $this->fp->seek($this->header->length + ($this->recordPos * $this->header->recordByteLength));

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

        $this->fp->seek($this->header->length + ($index * $this->header->recordByteLength));

        $this->record = RecordFactory::create($this, $this->recordPos, $this->fp->read($this->header->recordByteLength));

        return $this->record;
    }

    /**
     * @param $name
     *
     * @return ColumnInterface
     */
    public function getColumn($name)
    {
        foreach ($this->header->columns as $column) {
            if ($column->getName() === $name) {
                return $column;
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
        return $this->header->languageCode;
    }

    /**
     * @return ColumnInterface[]
     */
    public function getColumns(): array
    {
        return $this->header->columns;
    }

    public function getColumnCount(): int
    {
        return count($this->header->columns);
    }

    /**
     * @return int
     */
    public function getRecordCount()
    {
        return $this->header->recordCount;
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
        return $this->header->recordByteLength;
    }

    /**
     * @return string
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    public function getVersion(): int
    {
        return $this->header->getVersion();
    }

    /**
     * @see Codepage
     */
    public function getLanguageCode(): int
    {
        return $this->header->languageCode;
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

    public function getConvertFrom(): ?string
    {
        return $this->options['encoding'];
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
        return TableType::isFoxpro($this->header->getVersion());
    }

    public function getModifyDate()
    {
        return $this->header->modifyDate;
    }

    public function isInTransaction(): bool
    {
        return $this->header->inTransaction;
    }

    public function isEncrypted(): bool
    {
        return $this->header->encrypted;
    }

    public function getMdxFlag(): string
    {
        return chr($this->header->mdxFlag);
    }

    public function getHeaderLength(): int
    {
        return $this->header->length;
    }
}
