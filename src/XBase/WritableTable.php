<?php declare(strict_types=1);

namespace XBase;

use XBase\Column\ColumnInterface;
use XBase\Column\DBaseColumn;
use XBase\Enum\TableType;
use XBase\Exception\TableException;
use XBase\Memo\MemoFactory;
use XBase\Memo\MemoInterface;
use XBase\Memo\WritableMemoInterface;
use XBase\Record\RecordFactory;
use XBase\Record\RecordInterface;
use XBase\Stream\Stream;
use XBase\Traits\CloneTrait;

class WritableTable extends Table
{
    use CloneTrait;

    /**
     * Perform any edits on clone file and replace original file after call `save` method.
     */
    public const EDIT_MODE_CLONE = 'clone';

    /**
     * Perform edits immediately on original file.
     */
    public const EDIT_MODE_REALTIME = 'realtime';

    /**
     * @var bool
     *
     * @deprecated in 1.3
     */
    private $autoSave = false;

    /**
     * @var bool record property is new
     */
    private $insertion = false;

    protected function resolveOptions($options, $convertFrom = null): array
    {
        return array_merge(
            ['editMode' => self::EDIT_MODE_CLONE],
            parent::resolveOptions($options, $convertFrom)
        );
    }

    protected function open(): void
    {
        switch ($this->options['editMode']) {
            case self::EDIT_MODE_CLONE:
                $this->clone();
                $this->fp = Stream::createFromFile($this->cloneFilepath, 'rb+');
                break;

            case self::EDIT_MODE_REALTIME:
                $this->fp = Stream::createFromFile($this->filepath, 'rb+');
                break;
        }
    }

    protected function openMemo(): void
    {
        if (TableType::hasMemo($this->getVersion())) {
            $memoOptions = array_merge($this->options, ['writable' => true]);
            $this->memo = MemoFactory::create($this, $memoOptions);
        }
    }

    public function close(): void
    {
        if (self::EDIT_MODE_CLONE === $this->options['editMode'] && $this->autoSave) {
            @trigger_error('You should call `save` method directly.');
            $this->save();
        }

        parent::close();

        if ($this->cloneFilepath && file_exists($this->cloneFilepath)) {
            unlink($this->cloneFilepath);
        }
    }

    /**
     * @return WritableMemoInterface|null
     */
    public function getMemo(): ?MemoInterface
    {
        return $this->memo;
    }

    /**
     * @param $table
     *
     * @return WritableTable
     */
    public function cloneFrom(Table $table)
    {
        $result = new WritableTable($table->filepath);
        $result->version = $table->version;
        $result->modifyDate = $table->modifyDate;
        $result->recordCount = 0;
        $result->recordByteLength = $table->recordByteLength;
        $result->inTransaction = $table->inTransaction;
        $result->encrypted = $table->encrypted;
        $result->mdxFlag = $table->mdxFlag;
        $result->languageCode = $table->languageCode;
        $result->columns = $table->columns;
//        $result->columnNames = $table->columnNames;
        $result->headerLength = $table->headerLength;
        $result->backlist = $table->backlist;
        $result->foxpro = $table->isFoxpro();

        return $result;
    }

    /**
     * @param $filename
     * @param $fields
     *
     * @return bool|WritableTable
     */
    public function create($filename, $fields)
    {
        if (!$fields || !is_array($fields)) {
            throw new TableException('cannot create xbase with no fields', $this->filepath);
        }

        $recordByteLength = 1;
        $columns = [];
        $columnNames = [];
        $i = 0;

        foreach ($fields as $field) {
            if (!$field || !is_array($field) || sizeof($field) < 2) {
                throw new TableException('fields argument error, must be array of arrays', $this->filepath);
            }
            $column = new DBaseColumn($field[0], $field[1], 0, @$field[2], @$field[3], 0, 0, 0, 0, 0, 0, $i, $recordByteLength);
            $recordByteLength += $column->getLength();
            $columnNames[$i] = $field[0];
            $columns[$i] = $column;
            $i++;
        }

        $result = new WritableTable($filename);
        $result->version = TableType::DBASE_III_PLUS_MEMO;
        $result->modifyDate = time();
        $result->recordCount = 0;
        $result->recordByteLength = $recordByteLength;
        $result->inTransaction = 0;
        $result->encrypted = false;
        $result->mdxFlag = chr(0);
        $result->languageCode = chr(0);
        $result->columns = $columns;
//        $result->columnNames = $columnNames;
        $result->backlist = '';
        $result->foxpro = false;

        if ($result->openWrite($filename, true)) {
            return $result;
        }

        return false;
    }

    /**
     * @deprecated since 1.3 and will be deleted in 2.0. Do not use this.
     */
    public function openWrite($filename = false, $overwrite = false)
    {
        @trigger_error('Method `openWrite` is deprecated. Do not use it!');
        $this->autoSave = true;
//        if (!$filename) {
//            $filename = $this->filepath;
//        }
//
//        if (file_exists($filename) && !$overwrite) {
//            if ($this->fp = Stream::createFromFile($filename, 'r+')) {
//                $this->readHeader();
//            }
//        } elseif ($this->fp = Stream::createFromFile($filename, 'w+')) {
//            $this->writeHeader();
//        }
//
//        return false != $this->fp;
    }

    protected function writeHeader(): void
    {
//        $this->headerLength = ($this->isFoxpro() ? 296 : 33) + ($this->getColumnCount() * 32);

        $this->fp->seek(0);

        $this->fp->writeUChar($this->version); //0
        $this->fp->write3ByteDate(time()); //1-3
        $this->fp->writeUInt($this->recordCount); //4-7
        $this->fp->writeUShort($this->headerLength); //8-9
        $this->fp->writeUShort($this->recordByteLength); //10-11
        $this->fp->write(str_pad('', 2, chr(0))); //12-13
        $this->fp->write(chr($this->inTransaction ? 1 : 0)); //14
        $this->fp->write(chr($this->encrypted ? 1 : 0)); //15
        $this->fp->write(str_pad('', 4, chr(0))); //16-19 //todo-different-tabel
        $this->fp->write(str_pad('', 8, chr(0))); //20-27 //todo-different-tabel
        $this->fp->write($this->mdxFlag); //28
        $this->fp->write($this->languageCode); //29
        $this->fp->write(str_pad('', 2, chr(0))); //30-31 //todo-different-tabel

        if (in_array($this->getVersion(), [TableType::DBASE_7_MEMO, TableType::DBASE_7_NOMEMO])) {
            $this->fp->write(str_pad($this->languageName, 36, chr(0)));
        }

        foreach ($this->columns as $column) {
            $column->toBinaryString($this->fp);
        }

        $this->fp->writeUChar(0x0d);

        if (in_array($this->version, [
            TableType::VISUAL_FOXPRO,
            TableType::VISUAL_FOXPRO_AI,
            TableType::VISUAL_FOXPRO_VAR,
        ])) {
            $this->fp->write(str_pad($this->backlist, 263, ' '));
        }
    }

    public function appendRecord(): RecordInterface
    {
        $this->recordPos = $this->recordCount;
        $this->record = RecordFactory::create($this, $this->recordPos);
        $this->insertion = true;

        return $this->record;
    }

    public function writeRecord(RecordInterface $record = null): self
    {
        $record = $record ?? $this->record;
        if (!$record) {
            return $this;
        }

        $offset = $this->headerLength + ($record->getRecordIndex() * $this->recordByteLength);
        $this->fp->seek($offset);
        $this->fp->write(RecordFactory::createDataConverter($this)->toBinaryString($record));

        if ($this->insertion) {
            $this->recordCount++;
        }

        $this->fp->flush();

        if (self::EDIT_MODE_REALTIME === $this->options['editMode'] && $this->insertion) {
            $this->save();
        }

        $this->insertion = false;

        return $this;
    }

    public function deleteRecord(?RecordInterface $record = null): self
    {
        if ($this->record && $this->insertion) {
            $this->record = null;
            $this->recordPos = -1;

            return $this;
        }

        $record = $record ?? $this->record;
        if (!$record) {
            return $this;
        }

        $record->setDeleted(true);
        $this->writeRecord($record);

        return $this;
    }

    public function undeleteRecord()
    {
        $this->record->setDeleted(false);

        $this->fp->seek($this->headerLength + ($this->record->getRecordIndex() * $this->recordByteLength));
        $this->fp->write(' ');
        $this->fp->flush();
    }

    /**
     * Remove deleted records.
     */
    public function pack(): self
    {
        $newRecordCount = 0;
        for ($i = 0; $i < $this->getRecordCount(); $i++) {
            $r = $this->moveTo($i);

            if ($r->isDeleted()) {
                // remove memo columns
                foreach ($this->getMemoColumns() as $column) {
                    if ($pointer = $this->record->getGenuine($column->getName())) {
                        $this->getMemo()->delete($pointer);
                    }
                }
                continue;
            }

            $r->setRecordIndex($newRecordCount++);
            $this->writeRecord($r);
        }

        $this->recordCount = $newRecordCount;

        $size = $this->headerLength + ($this->recordCount * $this->recordByteLength);
        $this->fp->truncate($size);

        if (self::EDIT_MODE_REALTIME === $this->options['editMode']) {
            $this->save();
        }

        return $this;
    }

    public function save(): self
    {
        if ($this->memo) {
            $this->memo->save();
        }

        $this->writeHeader();
        //check end-of-file marker
        $stat = $this->fp->stat();
        $this->fp->seek($stat['size'] - 1);
        if (self::END_OF_FILE_MARKER !== ($lastByte = $this->fp->readUChar())) {
            $this->fp->writeUChar(self::END_OF_FILE_MARKER);
        }

        if (self::EDIT_MODE_CLONE === $this->options['editMode']) {
            copy($this->cloneFilepath, $this->filepath);
        }

        return $this;
    }

    /**
     * @internal
     *
     * @todo Find better solution to notify table from Memo.
     */
    public function onMemoBlocksDelete(array $blocks): void
    {
        $columns = $this->getMemoColumns();

        for ($i = 0; $i < $this->recordCount; $i++) {
            $record = $this->pickRecord($i);
            $save = false;
            foreach ($columns as $column) {
                if (!$pointer = $record->getGenuine($column->getName())) {
                    continue;
                }

                $sub = 0;
                foreach ($blocks as $deletedPointer => $length) {
                    if ($pointer && $pointer > $deletedPointer) {
                        $sub += $length;
                    }
                }
                $save = $sub > 0;
                $record->setGenuine($column->getName(), $pointer - $sub);
            }
            if ($save) {
                $this->writeRecord($record);
            }
        }
    }

    /**
     * @return ColumnInterface[]
     */
    private function getMemoColumns(): array
    {
        $result = [];
        foreach ($this->columns as $column) {
            if (in_array($column->getType(), TableType::getMemoTypes($this->version))) {
                $result[] = $column;
            }
        }

        return $result;
    }
}
