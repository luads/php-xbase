<?php

namespace XBase;

class Table
{
    /** @var string */
    protected $tableName;
    /** @var array|null */
    protected $availableColumns;
    /** @var resource */
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
    /** @var string */
    public $languageCode;
    /** @var Column[] */
    public $columns;
    /** @var int */
    public $headerLength;
    public $backlist;
    /** @var bool */
    public $foxpro;
    /** @var Memo */
    public $memoFile;

    /**
     * Table constructor.
     *
     * @param string $tableName
     * @param array|null $availableColumns
     * @param string|null $convertFrom Encoding of file
     * @throws \Exception
     */
    public function __construct($tableName, $availableColumns = null, $convertFrom = null)
    {
        $this->tableName = $tableName;
        $this->availableColumns = $availableColumns;
        $this->convertFrom = $convertFrom;
        $this->memoFile = new Memo($this, $this->tableName);
        $this->open();
    }

    /**
     * @return bool open successful
     */
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
        $this->foxpro = in_array($this->version, array(48, 49, 131, 203, 245, 251));
        $this->modifyDate = $this->read3ByteDate();
        $this->recordCount = $this->readInt();
        $this->headerLength = $this->readShort();
        $this->recordByteLength = $this->readShort();
        $this->readBytes(2); //reserved
        $this->inTransaction = $this->readByte() != 0;
        $this->encrypted = $this->readByte() != 0;
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

        for ($i = 0; $i < $fieldCount; $i++) {
            $column = new Column(
                strtolower($this->readString(11)), // name
                $this->readByte(),      // type
                $this->readInt(),       // memAddress
                $this->readChar(),      // length
                $this->readChar(),      // decimalCount
                $this->readBytes(2),    // reserved1
                $this->readChar(),      // workAreaID
                $this->readBytes(2),    // reserved2
                $this->readByte() != 0,   // setFields
                $this->readBytes(7),    // reserved3
                $this->readByte() != 0,   // indexed
                $j,                     // colIndex
                $bytepos                // bytePos
            );

            $bytepos += $column->getLength();

            if (!$this->availableColumns || ($this->availableColumns && in_array($column->name, $this->availableColumns))) {
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

    /**
     * @return bool|Record
     */
    public function nextRecord()
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
                return false;
            }

            $this->recordPos++;
            $this->record = new Record($this, $this->recordPos, $this->readBytes($this->recordByteLength));

            if ($this->record->isDeleted()) {
                $this->deleteCount++;
            } else {
                $valid = true;
            }
        } while (!$valid);

        return $this->record;
    }

    /**
     * @return bool|Record
     */
    public function previousRecord()
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
                return false;
            }

            $this->recordPos--;

            fseek($this->fp, $this->headerLength + ($this->recordPos * $this->recordByteLength));

            $this->record = new Record($this, $this->recordPos, $this->readBytes($this->recordByteLength));

            if ($this->record->isDeleted()) {
                $this->deleteCount++;
            } else {
                $valid = true;
            }
        } while (!$valid);

        return $this->record;
    }

    /**
     * @param int $index
     *
     * @return Record|null
     */
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

    /**
     * @param int $offset
     */
    private function setFilePos($offset)
    {
        $this->filePos = $offset;
        fseek($this->fp, $this->filePos);
    }

    /**
     * @return Record
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**
     * @param Column $column
     */
    public function addColumn($column)
    {
        $name = $nameBase = $column->getName();
        $index = 0;

        while (isset($this->columns[$name])) {
            $name = $nameBase . ++$index;
        }

        $column->name = $name;

        $this->columns[$name] = $column;
    }

    /**
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param $name
     *
     * @return Column
     */
    public function getColumn($name)
    {
        foreach ($this->columns as $column) {
            if ($column->name === $name) {
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

    /**
     * @return mixed
     */
    public function getRecordByteLength()
    {
        return $this->recordByteLength;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->tableName;
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

    /**
     * @param int $length
     *
     * @return bool|string
     */
    protected function readBytes($length)
    {
        $this->filePos += $length;

        return fread($this->fp, $length);
    }

    /**
     * @param string $buf
     *
     * @return bool|int
     */
    protected function writeBytes($buf)
    {
        return fwrite($this->fp, $buf);
    }

    /**
     * Read first byte
     *
     * @return bool|string
     */
    protected function readByte()
    {
        $this->filePos++;

        return fread($this->fp, 1);
    }

    /**
     * @param string $buf
     *
     * @return bool|int
     */
    protected function writeByte($buf)
    {
        return fwrite($this->fp, $buf);
    }

    /**
     * @param int $length
     *
     * @return bool|string
     */
    protected function readString($length)
    {
        return $this->readBytes($length);
    }

    /**
     * @param string $string
     *
     * @return bool|int
     */
    protected function writeString($string)
    {
        return $this->writeBytes($string);
    }

    /**
     * @return mixed
     */
    protected function readChar()
    {
        $buf = unpack('C', $this->readBytes(1));

        return $buf[1];
    }

    /**
     * @param $c
     *
     * @return bool|int
     */
    protected function writeChar($c)
    {
        $buf = pack('C', $c);

        return $this->writeBytes($buf);
    }

    /**
     * @return mixed
     */
    protected function readShort()
    {
        $buf = unpack('S', $this->readBytes(2));

        return $buf[1];
    }

    /**
     * @param $s
     *
     * @return bool|int
     */
    protected function writeShort($s)
    {
        $buf = pack('S', $s);

        return $this->writeBytes($buf);
    }

    /**
     * @return mixed
     */
    protected function readInt()
    {
        $buf = unpack('I', $this->readBytes(4));

        return $buf[1];
    }

    /**
     * @param $i
     *
     * @return bool|int
     */
    protected function writeInt($i)
    {
        $buf = pack('I', $i);

        return $this->writeBytes($buf);
    }

    /**
     * @return mixed
     */
    protected function readLong()
    {
        $buf = unpack('L', $this->readBytes(8));

        return $buf[1];
    }

    /**
     * {@inheritdoc}
     */
    protected function writeLong($l)
    {
        $buf = pack('L', $l);

        return $this->writeBytes($buf);
    }

    /**
     * @return int unixtime
     */
    protected function read3ByteDate()
    {
        $y = unpack('c', $this->readByte());
        $m = unpack('c', $this->readByte());
        $d = unpack('c', $this->readByte());

        return mktime(0, 0, 0, $m[1], $d[1], $y[1] > 70 ? 1900 + $y[1] : 2000 + $y[1]);
    }

    /**
     * @param $d
     *
     * @return bool|int
     */
    protected function write3ByteDate($d)
    {
        $t = getdate($d);

        return $this->writeChar($t['year'] % 1000) + $this->writeChar($t['mon']) + $this->writeChar($t['mday']);
    }

    /**
     * @return false|int
     */
    protected function read4ByteDate()
    {
        $y = $this->readShort();
        $m = unpack('c', $this->readByte());
        $d = unpack('c', $this->readByte());

        return mktime(0, 0, 0, $m[1], $d[1], $y);
    }

    /**
     * @param $d
     *
     * @return bool|int
     */
    protected function write4ByteDate($d)
    {
        $t = getdate($d);

        return $this->writeShort($t['year']) + $this->writeChar($t['mon']) + $this->writeChar($t['mday']);
    }
}
