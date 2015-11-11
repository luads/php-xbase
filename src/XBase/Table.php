<?php

namespace XBase;

class Table
{
    protected $tableName;
    protected $avaliableColumns;
    protected $fp;
    protected $filePos = 0;
    protected $recordPos = -1;
    protected $deleteCount = 0;
    protected $record;
    protected $convertFrom;

    public $version;
    public $modifyDate;
    public $recordCount;
    public $recordByteLength;
    public $inTransaction;
    public $encrypted;
    public $mdxFlag;
    public $languageCode;
    public $columns;
    public $headerLength;
    public $backlist;
    public $foxpro;

    public function __construct($tableName, $avaliableColumns = null, $convertFrom = null)
    {
        $this->tableName = $tableName;
        $this->avaliableColumns = $avaliableColumns;
        $this->convertFrom = $convertFrom;
        $this->open();
    }

    protected function open()
    {
        if (!file_exists($this->tableName)) {
            throw new \Exception(sprintf('File %s cannot be found', $this->tableName));
        }

        $this->fp = fopen($this->tableName, 'rb');
        $this->readHeader();

        return $this->fp != false;
    }

    protected function readHeader()
    {
        $this->version = $this->readChar();
        $this->foxpro = $this->version==48 || $this->version==49 || $this->version==245 || $this->version==251;
        $this->modifyDate = $this->read3ByteDate();
        $this->recordCount = $this->readInt();
        $this->headerLength = $this->readShort();
        $this->recordByteLength = $this->readShort();
        $this->readBytes(2); //reserved
        $this->inTransaction = $this->readByte()!=0;
        $this->encrypted = $this->readByte()!=0;
        $this->readBytes(4); //Free record thread
        $this->readBytes(8); //Reserved for multi-user dBASE
        $this->mdxFlag = $this->readByte();
        $this->languageCode = $this->readByte();
        $this->readBytes(2); //reserved

        $fieldCount = floor(($this->headerLength - ($this->foxpro ? 296 : 33)) / 32);

        /* some checking */
        if ($this->headerLength > filesize($this->tableName)) {
            throw new Exception\TableException(sprintf('File %s is not DBF', $this->tableName));
        }

        if ($this->headerLength + ($this->recordCount * $this->recordByteLength) - 500 > filesize($this->tableName)) {
            throw new Exception\TableException(sprintf('File %s is not DBF', $this->tableName));
        }

        /* columns */
        $this->columns = array();
        $bytepos = 1;
        $j = 0;

        for ($i=0;$i<$fieldCount;$i++) {
            $column = new Column(
                strtolower($this->readString(11)), // name
                $this->readByte(),      // type
                $this->readInt(),       // memAddress
                $this->readChar(),      // length
                $this->readChar(),      // decimalCount
                $this->readBytes(2),    // reserved1
                $this->readChar(),      // workAreaID
                $this->readBytes(2),    // reserved2
                $this->readByte()!=0,   // setFields
                $this->readBytes(7),    // reserved3
                $this->readByte()!=0,   // indexed
                $j,                     // colIndex
                $bytepos                // bytePos
            );

            $bytepos += $column->getLength();

            if (!$this->avaliableColumns || ($this->avaliableColumns && in_array($column->name, $this->avaliableColumns))) {
                $this->addColumn($column);
                $j++;
            }
        }

        if ($this->foxpro) {
            $this->backlist = $this->readBytes(263);
        }

        $this->setFilePos($this->headerLength);
        $this->recordPos = -1;
        $this->record = false;
        $this->deleteCount = 0;
    }

    public function close()
    {
        fclose($this->fp);
    }

    public function nextRecord()
    {
        if (!$this->isOpen()) {
            $this->open();
        }

        $valid = false;

        do {
            if (($this->recordPos + 1) >= $this->recordCount) {
                return false;
            }

            $this->recordPos++;
            $this->record = new Record($this, $this->recordPos, $this->readBytes($this->recordByteLength));

            if ($this->record->isDeleted()) {
                $this->deleteCount++;
            } else {
                $valid=true;
            }
        } while (!$valid);

        return $this->record;
    }

    public function previousRecord()
    {
        if (!$this->isOpen()) {
            $this->open();
        }

        $valid = false;

        do {
            if (($this->recordPos - 1) < 0) {
                return false;
            }

            $this->recordPos--;

	        fseek($this->fp, $this->headerLength + ( $this->recordPos * $this->recordByteLength));

            $this->record = new Record($this, $this->recordPos, $this->readBytes($this->recordByteLength));

            if ($this->record->isDeleted()) {
                $this->deleteCount++;
            } else {
                $valid=true;
            }
        } while (!$valid);

        return $this->record;
    }

    public function moveTo($index)
    {
        $this->recordPos = $index;

        if ($index < 0) {
            return null;
        }

        fseek($this->fp, $this->headerLength + ($index * $this->recordByteLength));

        $this->record = new Record($this, $this->recordPos, $this->readBytes($this->recordByteLength));

        return $this->record;
    }

    private function setFilePos($offset)
    {
        $this->filePos = $offset;
        fseek($this->fp, $this->filePos);
    }

    public function getRecord()
    {
        return $this->record;
    }

    public function addColumn($column)
    {
        $name = $nameBase = $column->getName();
        $index = 0;

        while (isset($this->columns[$name]))
        {
            $name = $nameBase . ++$index;
        }

        $column->name = $name;

        $this->columns[$name] = $column;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getColumn($name)
    {
        foreach ($this->columns as $column)
        {
            if ($column->name === $name)
            {
                return $column;
            }
        }

        throw new \Exception(sprintf('Column %s not found', $name));
    }

    public function getColumnCount()
    {
        return count($this->columns);
    }

    public function getRecordCount()
    {
        return $this->recordCount;
    }

    public function getRecordPos()
    {
        return $this->recordPos;
    }

    public function getRecordByteLength()
    {
        return $this->recordByteLength;
    }

    public function getName()
    {
        return $this->tableName;
    }

    public function getDeleteCount()
    {
        return $this->deleteCount;
    }

    public function getConvertFrom()
    {
        return $this->convertFrom;
    }

    protected function isOpen()
    {
        return $this->fp ? true : false;
    }


    protected function readBytes($l)
    {
        $this->filePos += $l;

        return fread($this->fp, $l);
    }

    protected function writeBytes($buf)
    {
        return fwrite($this->fp, $buf);
    }

    protected function readByte()
    {
        $this->filePos++;

        return fread($this->fp, 1);
    }

    protected function writeByte($b)
    {
        return fwrite($this->fp, $b);
    }

    protected function readString($l)
    {
        return $this->readBytes($l);
    }

    protected function writeString($s)
    {
        return $this->writeBytes($s);
    }

    protected function readChar()
    {
        $buf = unpack('C', $this->readBytes(1));

        return $buf[1];
    }

    protected function writeChar($c)
    {
        $buf = pack('C', $c);

        return $this->writeBytes($buf);
    }

    protected function readShort()
    {
        $buf = unpack('S', $this->readBytes(2));

        return $buf[1];
    }

    protected function writeShort($s)
    {
        $buf = pack('S', $s);

        return $this->writeBytes($buf);
    }

    protected function readInt()
    {
        $buf = unpack('I', $this->readBytes(4));

        return $buf[1];
    }

    protected function writeInt($i)
    {
        $buf = pack('I', $i);

        return $this->writeBytes($buf);
    }

    protected function readLong()
    {
        $buf = unpack('L', $this->readBytes(8));

        return $buf[1];
    }

    protected function writeLong($l)
    {
        $buf = pack('L', $l);

        return $this->writeBytes($buf);
    }

    protected function read3ByteDate()
    {
        $y = unpack('c', $this->readByte());
        $m = unpack('c', $this->readByte());
        $d = unpack('c', $this->readByte());

        return mktime(0, 0, 0, $m[1], $d[1] ,$y[1] > 70 ? 1900 + $y[1] : 2000 + $y[1]);
    }

    protected function write3ByteDate($d)
    {
        $t = getdate($d);

        return $this->writeChar($t['year'] % 1000) + $this->writeChar($t['mon']) + $this->writeChar($t['mday']);
    }

    protected function read4ByteDate()
    {
        $y = readShort();
        $m = unpack('c',$this->readByte());
        $d = unpack('c',$this->readByte());

        return mktime(0, 0, 0, $m[1], $d[1], $y);
    }

    protected function write4ByteDate($d)
    {
        $t = getdate($d);

        return $this->writeShort($t['year']) + $this->writeChar($t['mon']) + $this->writeChar($t['mday']);
    }
}
