<?php

namespace XBase;

use XBase\Column\DBaseColumn;
use XBase\Enum\TableType;
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
            throw new Exception\TableException('cannot create xbase with no fields', $this->filepath);
        }

        $recordByteLength = 1;
        $columns = [];
        $columnNames = [];
        $i = 0;

        foreach ($fields as $field) {
            if (!$field || !is_array($field) || sizeof($field) < 2) {
                throw new Exception\TableException('fields argument error, must be array of arrays', $this->filepath);
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
        $result->columnNames = $columnNames;
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

    public function writeHeader()
    {
        $this->headerLength = ($this->foxpro ? 296 : 33) + ($this->getColumnCount() * 32);

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
            $this->fp->write(str_pad(substr($column->getRawName(), 0, 11), 11, chr(0)));
            $this->fp->write($column->getType());
            $this->fp->writeUInt($column->getMemAddress());
            $this->fp->writeUChar($column->getDataLength());
            $this->fp->writeUChar($column->getDecimalCount());
            $this->fp->write(str_pad('', 2, chr(0)));
            $this->fp->writeUChar($column->getWorkAreaID());
            $this->fp->write(str_pad('', 2, chr(0)));
            $this->fp->write(chr($column->isSetFields() ? 1 : 0));
            $this->fp->write(str_pad('', 7, chr(0)));
            $this->fp->write(chr($column->isIndexed() ? 1 : 0));
        }

        if ($this->foxpro) {
            $this->fp->write(str_pad($this->backlist, 263, ' '));
        }

        $this->fp->writeUChar(0x0d);
    }

    /**
     * @return Record
     */
    public function appendRecord()
    {
        $this->record = new Record($this, $this->recordCount);
        $this->recordCount += 1;

        return $this->record;
    }

    public function writeRecord()
    {
        $p = $this->fp->seek($this->headerLength + ($this->record->getRecordIndex() * $this->recordByteLength));
        $data = $this->record->serializeRawData(); // removed referencing
        $p = $this->fp->write($data);

        if ($this->record->isInserted()) {
            $this->writeHeader();
        }

        $this->fp->flush();
    }

    public function deleteRecord()
    {
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
    public function pack()
    {
        $newRecordCount = 0;
        $newFilepos = $this->headerLength;

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

//    /**
//     * {{@inheritdoc}}
//     */
//    protected function writes($buf)
//    {
//        return fwrite($this->fp, $buf);
//    }
//
//    /**
//     * {{@inheritdoc}}
//     */
//    protected function write($buf)
//    {
//        return fwrite($this->fp, $buf);
//    }
//
//    /**
//     * {{@inheritdoc}}
//     */
//    protected function writeString($string)
//    {
//        return $this->fp->writes($string);
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    protected function writeChar($c)
//    {
//        $buf = pack("C", $c);
//
//        return $this->fp->writes($buf);
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    protected function writeShort($s)
//    {
//        $buf = pack("S", $s);
//
//        return $this->fp->writes($buf);
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    protected function writeInt($i)
//    {
//        $buf = pack("I", $i);
//
//        return $this->fp->writes($buf);
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    protected function writeLong($l)
//    {
//        $buf = pack("L", $l);
//
//        return $this->fp->writes($buf);
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    protected function write3ByteDate($d)
//    {
//        $t = getdate($d);
//
//        return $this->fp->writeChar($t["year"] % 1000) + $this->fp->writeChar($t["mon"]) + $this->fp->writeChar($t["mday"]);
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    protected function write4ByteDate($d)
//    {
//        $t = getdate($d);
//
//        return $this->fp->writeShort($t["year"]) + $this->fp->writeChar($t["mon"]) + $this->fp->writeChar($t["mday"]);
//    }
}
