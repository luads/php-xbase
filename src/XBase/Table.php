<?php declare(strict_types=1);

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

    /** @var string|null */
    protected $convertFrom;

    /**
     * @var MemoInterface|null
     */
    protected $memo;

    /** @var string|null DBase7 only */
    protected $languageName;

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
    public function __construct(string $filepath, $options = [], $convertFrom = null)
    {
        $this->filepath = $filepath;
        $this->options = $this->resolveOptions($options, $convertFrom);

        $this->open();
        $this->readHeader();
        $this->openMemo();
    }

    protected function resolveOptions($options, $convertFrom = null): array
    {
        // right options
        if (!empty($options) && array_intersect(['encoding', 'columns'], array_keys($options))) {
            return array_merge([
                'columns'  => [],
                'encoding' => null,
            ], $options);
        }

        if (!empty($options)) {
            @trigger_error('You should pass availableColumns as `columns` option');
        }
        if (!empty($convertFrom)) {
            @trigger_error('You should pass convertFrom as `encoding` option');
        }

        return [
            'columns'  => $options ?? [],
            'encoding' => $convertFrom,
        ];
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
        $this->header = HeaderBuilderFactory::create($this->filepath)
            ->build()
            ->getHeader();
        $this->fp->seek($this->header->getLength());

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

    /**
     * @return array [$fieldCount, $terminatorLength]
     */
    protected function pickColumnsCount(): array
    {
        // some files has headers with 2byte-terminator 0xOD00
        foreach ([1, 2] as $terminatorLength) {
            $fieldCount = $this->getLogicalFieldCount($terminatorLength);
            if (is_int($fieldCount)) {
                return [$fieldCount, $terminatorLength];
            }
        }

        throw new \LogicException('Wrong fieldCount calculation');
    }

    protected function readColumns(int $columnsCount): void
    {
        /* some checking */
        clearstatcache();
        if ($this->headerLength > filesize($this->filepath)) {
            throw new TableException(sprintf('File %s is not DBF', $this->filepath));
        }

        if ($this->headerLength + ($this->recordCount * $this->recordByteLength) - 500 > filesize($this->filepath)) {
            throw new TableException(sprintf('File %s is not DBF', $this->filepath));
        }

        /* columns */
        $this->columns = [];
        $bytePos = 1;

        $class = ColumnFactory::getClass($this->getVersion());
        $index = 0;
        for ($i = 0; $i < $columnsCount; $i++) {
            /** @var ColumnInterface $column */
            $column = $class::create($this->fp->read(call_user_func([$class, 'getHeaderLength'])), $index++, $bytePos);
            $bytePos += $column->getLength();
            $this->addColumn($column);
        }
    }

    /**
     * @return float|int
     */
    protected function getLogicalFieldCount(int $terminatorLength = 1)
    {
        $headerLength = self::HEADER_LENGTH + $terminatorLength; // [Terminator](1)
        $fieldLength = self::FIELD_LENGTH;
        if (in_array($this->getVersion(), [TableType::DBASE_7_MEMO, TableType::DBASE_7_NOMEMO])) {
            $headerLength += 36; // [Language driver name](32) + [Reserved](4) +
            $fieldLength = DBase7Column::getHeaderLength();
        }
        $backlist = TableType::isVisualFoxpro($this->version) ? self::VFP_BACKLIST_LENGTH : 0;
        $extraSize = $this->headerLength - ($headerLength + $backlist);

        return $extraSize / $fieldLength;
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
     * @param int $offset
     */
    private function setFilePos($offset)
    {
        $this->filePos = $offset;
        $this->fp->seek($this->filePos);
    }

    /**
     * @throws TableException
     */
    private function checkHeaderTerminator(int $terminatorLength): void
    {
        $terminator = $this->fp->read($terminatorLength);
        switch ($terminatorLength) {
            case 1:
                if (chr(0x0D) !== $terminator) {
                    throw new TableException('Expected header terminator not present at position '.$this->fp->tell());
                }
                break;

            case 2:
                $unpack = unpack('n', $terminator);
                if (0x0D00 !== $unpack[1]) {
                    throw new TableException('Expected header terminator not present at position '.$this->fp->tell());
                }
                break;
        }
    }

    public function addColumn(ColumnInterface $column): self
    {
        $name = $nameBase = $column->getName();
        $index = 0;

        while (isset($this->columns[$name])) {
            $name = $nameBase.++$index;
        }

        $this->columns[$name] = $column;

        return $this;
    }

    public function getColumn(string $name): ColumnInterface
    {
        foreach ($this->header->getColumns() as $column) {
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
        return $this->header->getLanguageCode();
    }

    /**
     * @return ColumnInterface[]
     */
    public function getColumns(): array
    {
        return $this->header->getColumns();
    }

    public function getColumnCount(): int
    {
        return count($this->getColumns());
    }

    public function getRecordCount(): int
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

    public function getVersion(): int
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
