<?php

namespace XBase;

class WritableTable extends Table
{    
    public function cloneFrom($table)
    {
        $result = new WritableTable($table->tableName);
        $result->version=$table->version;
        $result->modifyDate=$table->modifyDate;
        $result->recordCount=0;
        $result->recordByteLength=$table->recordByteLength;
        $result->inTransaction=$table->inTransaction;
        $result->encrypted=$table->encrypted;
        $result->mdxFlag=$table->mdxFlag;
        $result->languageCode=$table->languageCode;
        $result->columns=$table->columns;
        $result->columnNames=$table->columnNames;
        $result->headerLength=$table->headerLength;
        $result->backlist=$table->backlist;
        $result->foxpro=$table->foxpro;
        
        return $result;
    }

    public function create($filename, $fields)
    {        
        if (!$fields || !is_array($fields)) {
            throw new Exception\TableException("cannot create xbase with no fields", $this->tableName);
        }
        
        $recordByteLength = 1;
        $columns = array();
        $columnNames = array();
        $i = 0;
        
        foreach ($fields as $field) {
            if (!$field || !is_array($field) || sizeof($field)<2) {
                throw new Exception\TableException("fields argument error, must be array of arrays", $this->tableName);
            }
            $column = new Column($field[0], $field[1], 0, @$field[2], @$field[3], 0, 0, 0, 0, 0, 0, $i, $recordByteLength);
            $recordByteLength += $column->getDataLength();
            $columnNames[$i] = $field[0];
            $columns[$i] = $column;
            $i++;
        }
        
        $result = new WritableTable($filename);
        $result->version = 131;
        $result->modifyDate = time();
        $result->recordCount = 0;
        $result->recordByteLength = $recordByteLength;
        $result->inTransaction = 0;
        $result->encrypted = false;
        $result->mdxFlag = chr(0);
        $result->languageCode = chr(0);
        $result->columns = $columns;
        $result->columnNames = $columnNames;
        $result->backlist = "";
        $result->foxpro = false;
        
        if ($result->openWrite($filename, true)) {
            return $result;
        }
        
        return false;
    }

    public function openWrite($filename=false, $overwrite=false)
    {
        if (!$filename) {
            $filename = $this->tableName;
        }
        
        if (file_exists($filename) && !$overwrite) {
            if ($this->fp = fopen($filename, "r+")) {
                $this->readHeader();
            }
        } else {
            if ($this->fp = fopen($filename, "w+")) {
                $this->writeHeader();
            }
        }
        
        return $this->fp!=false;
    }
    
    public function writeHeader()
    {
        $this->headerLength=($this->foxpro?296:33) + ($this->getColumnCount()*32);
        
        fseek($this->fp, 0);
        
        $this->writeChar($this->version);
        $this->write3ByteDate(time());
        $this->writeInt($this->recordCount);
        $this->writeShort($this->headerLength);
        $this->writeShort($this->recordByteLength);
        $this->writeBytes(str_pad("", 2, chr(0)));
        $this->writeByte(chr($this->inTransaction?1:0));
        $this->writeByte(chr($this->encrypted?1:0));
        $this->writeBytes(str_pad("", 4, chr(0)));
        $this->writeBytes(str_pad("", 8, chr(0)));
        $this->writeByte($this->mdxFlag);
        $this->writeByte($this->languageCode);
        $this->writeBytes(str_pad("", 2, chr(0)));
        
        foreach ($this->columns as $column) {
            $this->writeString(str_pad(substr($column->rawname, 0, 11), 11, chr(0)));
            $this->writeByte($column->type);
            $this->writeInt($column->getMemAddress());
            $this->writeChar($column->getDataLength());
            $this->writeChar($column->getDecimalCount());
            $this->writeBytes(str_pad("", 2, chr(0)));
            $this->writeChar($column->getWorkAreaID());
            $this->writeBytes(str_pad("", 2, chr(0)));
            $this->writeByte(chr($column->isSetFields()?1:0));
            $this->writeBytes(str_pad("", 7, chr(0)));
            $this->writeByte(chr($column->isIndexed()?1:0));
        }
        
        if ($this->foxpro) {
            $this->writeBytes(str_pad($this->backlist, 263, " "));
        }
        
        $this->writeChar(0x0d);
    }

    public function appendRecord()
    {
        $this->record = new Record($this, $this->recordCount);
        $this->recordCount += 1;
        
        return $this->record;
    }

    public function writeRecord()
    {
        fseek($this->fp, $this->headerLength+($this->record->getRecordIndex()*$this->recordByteLength));
        $data = $this->record->serializeRawData(); // removed referencing
        fwrite($this->fp, $data);
        
        if ($this->record->isInserted()) {
            $this->writeHeader();
        }

        fflush($this->fp);
    }

    public function deleteRecord()
    {
        $this->record->deleted = true;
        
        fseek($this->fp, $this->headerLength+($this->record->getRecordIndex()*$this->recordByteLength));
        fwrite($this->fp, "!");
        fflush($this->fp);
    }

    public function undeleteRecord()
    {
        $this->record->deleted = false;
        
        fseek($this->fp, $this->headerLength+($this->record->getRecordIndex()*$this->recordByteLength));
        fwrite($this->fp, " ");
        fflush($this->fp);
    }

    public function pack()
    {
        $newRecordCount = 0;
        $newFilepos = $this->headerLength;
        
        for ($i=0; $i < $this->getRecordCount(); $i++) {
            $r = $this->moveTo($i);
            
            if ($r->isDeleted()) {
                continue;
            }
            
            $r->recordIndex = $newRecordCount++;
            $this->writeRecord();
        }
        
        $this->recordCount = $newRecordCount;
        $this->writeHeader();
        
        ftruncate($this->fp, $this->headerLength+($this->recordCount*$this->recordByteLength));
    }
    
    protected function writeBytes($buf)
    {
        return fwrite($this->fp, $buf);
    }

    protected function writeByte($b)
    {
        return fwrite($this->fp, $b);
    }

    protected function writeString($s)
    {
        return $this->writeBytes($s);
    }

    protected function writeChar($c)
    {
        $buf = pack("C", $c);
        
        return $this->writeBytes($buf);
    }

    protected function writeShort($s)
    {
        $buf = pack("S", $s);
        
        return $this->writeBytes($buf);
    }

    protected function writeInt($i)
    {
        $buf = pack("I", $i);
        
        return $this->writeBytes($buf);
    }

    protected function writeLong($l)
    {
        $buf = pack("L", $l);
        
        return $this->writeBytes($buf);
    }

    protected function write3ByteDate($d)
    {
        $t = getdate($d);
        
        return $this->writeChar($t["year"] % 1000) + $this->writeChar($t["mon"]) + $this->writeChar($t["mday"]);
    }

    protected function write4ByteDate($d)
    {
        $t = getdate($d);
        
        return $this->writeShort($t["year"]) + $this->writeChar($t["mon"]) + $this->writeChar($t["mday"]);
    }
}
