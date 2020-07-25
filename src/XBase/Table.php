<?php

namespace XBase;

use XBase\Column\ColumnFactory;
use XBase\Column\ColumnInterface;
use XBase\Column\DBase7Column;
use XBase\Enum\TableType;
use XBase\Exception\TableException;
use XBase\Memo\MemoFactory;
use XBase\Memo\MemoInterface;
use XBase\Record\RecordFactory;
use XBase\Record\RecordInterface;
use XBase\Stream\Stream;

class Table
{
    /** @var int Table header length in bytes */
    const HEADER_LENGTH = 32;
    /** @var int Record field length in bytes */
    const FIELD_LENGTH = 32;

    /** @var int Visual FoxPro backlist length */
    const VFP_BACKLIST_LENGTH = 263;

    /** @var string Table filepath. */
    protected $filepath;
    /** @var array|null */
    protected $availableColumns;
    /** @var Stream */
    protected $fp;
    /** @var int */
    protected $filePos = 0;
    /** @var int */
    protected $recordPos = -1;
    /** @var int */
    protected $deleteCount = 0;
    /** @var Record */
    protected $record;
    /** @var string|null */
    protected $convertFrom;

    /** @var string */
    public $version;
    /** @var int unixtime */
    public $modifyDate;
    /** @var int */
    public $recordCount;
    /** @var int */
    public $recordByteLength;
    /** @var bool */
    public $inTransaction;
    /** @var bool */
    public $encrypted;
    /** @var string */
    public $mdxFlag;
    /**
     * @var string Language codepage.
     * @see https://blog.codetitans.pl/post/dbf-and-language-code-page/
     */
    public $languageCode;

    /** @var ColumnInterface[] */
    public $columns;
    /** @var int */
    public $headerLength;
    public $backlist;
    /**
     * @var bool
     * @deprecated since 1.1 and will be removed in 2.0. Use isFoxpro method instead.
     */
    public $foxpro;
    /** @var MemoInterface */
    public $memo;

    /**
     * Table constructor.
     *
     * @param string      $filepath
     * @param array|null  $availableColumns
     * @param string|null $convertFrom      Encoding of file
     *
     * @throws \Exception
     */
    public function __construct($filepath, $availableColumns = null, $convertFrom = null)
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
        $this->version = $this->fp->readUChar();
        $this->foxpro = TableType::isFoxpro($this->version);
        $this->modifyDate = $this->fp->read3ByteDate();
        $this->recordCount = $this->fp->readUInt();
        $this->headerLength = $this->fp->readUShort();
        $this->recordByteLength = $this->fp->readUShort();
        $this->fp->read(2); //reserved
        $this->inTransaction = 0 != $this->fp->read();
        $this->encrypted = 0 != $this->fp->read();
        $this->fp->read(4); //Free record thread
        $this->fp->read(8); //Reserved for multi-user dBASE
        $this->mdxFlag = $this->fp->read();
        $this->languageCode = $this->fp->read();
        $this->fp->read(2); //reserved
        if (in_array($this->getVersion(), [TableType::DBASE_7_MEMO, TableType::DBASE_7_NOMEMO])) {
            $languageName = rtrim($this->fp->read(32), chr(0));
            $this->fp->read(4);
        }

        [$columnsCount, $terminatorLength] = $this->pickColumnsCount();
        $this->readColumns($columnsCount);
        $this->checkHeaderTerminator($terminatorLength);

        if (TableType::isVisualFoxpro($this->version)) {
            $this->backlist = $this->fp->read(self::VFP_BACKLIST_LENGTH);
        }

//        $this->setFilePos($this->headerLength);
        $this->recordPos = -1;
        $this->record = false;
        $this->deleteCount = 0;
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
            if (($this->recordPos + 1) >= $this->recordCount) {
                return null;
            }

            $this->recordPos++;
            $this->record = RecordFactory::create($this, $this->recordPos, $this->fp->read($this->recordByteLength));

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
        if ($position >= $this->recordCount) {
            throw new TableException("Row with index {$position} does not exists");
        }

        $curPos = $this->fp->tell();
        $seekPos = $this->headerLength + $position * $this->recordByteLength;
        if (0 !== $this->fp->seek($seekPos)) {
            throw new TableException("Failed to pick row at position {$position}");
        }

        $record = RecordFactory::create($this, $position, $this->fp->read($this->recordByteLength));
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

            $this->fp->seek($this->headerLength + ($this->recordPos * $this->recordByteLength));

            $this->record = RecordFactory::create($this, $this->recordPos, $this->fp->read($this->recordByteLength));

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

        $this->fp->seek($this->headerLength + ($index * $this->recordByteLength));

        $this->record = RecordFactory::create($this, $this->recordPos, $this->fp->read($this->recordByteLength));

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
     * @return Record
     */
    public function getRecord()
    {
        return $this->record;
    }

    public function getCodepage(): int
    {
        return ord($this->languageCode);
    }

    public function addColumn(ColumnInterface $column)
    {
        $name = $nameBase = $column->getName();
        $index = 0;

        while (isset($this->columns[$name])) {
            $name = $nameBase.++$index;
        }

        $this->columns[$name] = $column;
    }

    /**
     * @return ColumnInterface[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param $name
     *
     * @return ColumnInterface
     */
    public function getColumn($name)
    {
        foreach ($this->columns as $column) {
            if ($column->getName() === $name) {
                return $column;
            }
        }

        throw new \Exception(sprintf('Column %s not found', $name));
    }

    /**
     * @return int
     */
    public function getColumnCount()
    {
        return count($this->columns);
    }

    /**
     * @return int
     */
    public function getRecordCount()
    {
        return $this->recordCount;
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
        return $this->recordByteLength;
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
        return $this->version;
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
        return TableType::isFoxpro($this->version);
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
}
