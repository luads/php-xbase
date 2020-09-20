<?php

namespace XBase;

use XBase\Column\DBaseColumn;
use XBase\Enum\TableType;
use XBase\Exception\TableException;
use XBase\Record\RecordFactory;
use XBase\Record\RecordInterface;
use XBase\Stream\Stream;

class WritableTable extends Table
{
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
            $recordByteLength += $column->getDataLength();
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
     * @param bool $filename
     * @param bool $overwrite
     *
     * @return bool
     */
    public function openWrite($filename = false, $overwrite = false)
    {
        if (!$filename) {
            $filename = $this->filepath;
        }

        if (file_exists($filename) && !$overwrite) {
            if ($this->fp = Stream::createFromFile($filename, 'r+')) {
                $this->readHeader();
            }
        } else {
            if ($this->fp = Stream::createFromFile($filename, 'w+')) {
                $this->writeHeader();
            }
        }

        return false != $this->fp;
    }

    public function writeHeader(): void
    {
        $this->headerLength = ($this->isFoxpro() ? 296 : 33) + ($this->getColumnCount() * 32);

        $this->fp->seek(0);

        $this->fp->writeUChar($this->version);
        $this->fp->write3ByteDate(time());
        $this->fp->writeUInt($this->recordCount);
        $this->fp->writeUShort($this->headerLength);
        $this->fp->writeUShort($this->recordByteLength);
        $this->fp->write(str_pad('', 2, chr(0)));
        $this->fp->write(chr($this->inTransaction ? 1 : 0));
        $this->fp->write(chr($this->encrypted ? 1 : 0));
        $this->fp->write(str_pad('', 4, chr(0)));
        $this->fp->write(str_pad('', 8, chr(0)));
        $this->fp->write($this->mdxFlag);
        $this->fp->write($this->languageCode);
        $this->fp->write(str_pad('', 2, chr(0)));

        foreach ($this->columns as $column) {
            $this->fp->write(str_pad(substr($column->getRawName(), 0, 11), 11, chr(0))); // 0-10
            $this->fp->write($column->getType());// 11
            $this->fp->writeUInt($column->getMemAddress());//12-15
            $this->fp->writeUChar($column->getDataLength());//16
            $this->fp->writeUChar($column->getDecimalCount());//17
            $this->fp->write(
                method_exists($column, 'getReserved1')
                    ? call_user_func([$column, 'getReserved1'])
                    : str_pad('', 2, chr(0))
            );//18-19
            $this->fp->writeUChar($column->getWorkAreaID());//20
            $this->fp->write(
                method_exists($column, 'getReserved2')
                    ? call_user_func([$column, 'getReserved2'])
                    : str_pad('', 2, chr(0))
            );//21-22
            $this->fp->write(chr($column->isSetFields() ? 1 : 0));//23
            $this->fp->write(
                method_exists($column, 'getReserved3')
                    ? call_user_func([$column, 'getReserved3'])
                    : str_pad('', 7, chr(0))
            );//24-30
            $this->fp->write(chr($column->isIndexed() ? 1 : 0));//31
        }

        $this->fp->writeUChar(0x0d);

        if ($this->foxpro) {
            $this->fp->write(str_pad($this->backlist, 263, ' '));
        }
    }

    /**
     * @return RecordInterface
     */
    public function appendRecord()
    {
        $this->recordPos = $this->recordCount++;
        $this->record = RecordFactory::create($this, $this->recordPos);

        return $this->record;
    }

    public function writeRecord(): void
    {
        if (!$this->record) {
            return;
        }

        $offset = $this->headerLength + ($this->record->getRecordIndex() * $this->recordByteLength);
        $this->fp->seek($offset);
        $data = $this->record->serializeRawData(); // todo build binary string
        $this->fp->write($data);

        if ($this->record->isInserted()) {
            $this->writeHeader();
        }

        $this->fp->flush();

        $this->record->setInserted(false);
    }

    public function deleteRecord(): void
    {
        if ($this->record->isInserted()) {
            $this->record = null;
            $this->recordPos = -1;
            return;
        }

        $this->record->setDeleted(true);

        $this->fp->seek($this->headerLength + ($this->record->getRecordIndex() * $this->recordByteLength));
        $this->fp->write('!');
        $this->fp->flush();
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
    public function pack(): void
    {
        $newRecordCount = 0;
        for ($i = 0; $i < $this->getRecordCount(); $i++) {
            $r = $this->moveTo($i);

            if ($r->isDeleted()) {
                continue;
            }

            $r->setRecordIndex($newRecordCount++);
            $this->writeRecord();
        }

        $this->recordCount = $newRecordCount;
        $this->writeHeader();

        $this->fp->truncate($this->headerLength + ($this->recordCount * $this->recordByteLength));
    }
}
