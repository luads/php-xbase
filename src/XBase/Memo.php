<?php

namespace XBase;

class Memo
{
    /** @var resource */
    protected $fp;
    /** @var Table */
    protected $table;
    /** @var string */
    protected $tableName;

    /**
     * Memo constructor.
     *
     * @param Table $table
     * @param string $tableName
     */
    public function __construct(Table $table, $tableName)
    {
        $this->table = $table;
        $this->tableName = $tableName;
        $this->open();
    }

    /**
     * @return bool
     */
    protected function open()
    {
        $fileName = str_replace(array("dbf", "DBF"), array("fpt", "FPT"), $this->tableName);

        if (!file_exists($fileName)) {
            return false;
        }

        $this->fp = fopen($fileName, 'rb');

        return $this->fp != false;
    }

    /**
     * @param int $pointer
     *
     * @return false|string|null
     */
    public function get($pointer)
    {
        $value = null;
        if ($this->fp && $pointer != 0) {
            // Getting block size
            fseek($this->fp, 6);
            $data = unpack("n", fread($this->fp, 2));
            $memoBlockSize = $data[1];

            fseek($this->fp, $pointer * $memoBlockSize);
            $type = unpack("N", fread($this->fp, 4));
            if ($type[1] == "1") {
                $len = unpack("N", fread($this->fp, 4));
                $value = trim(fread($this->fp, $len[1]));
                if ($this->table->getConvertFrom()) {
                    $value = iconv($this->table->getConvertFrom(), 'utf-8', $value);
                }
            } else {
                // Pictures will not be shown
                $value = "{BINARY_PICTURE}";
            }
        }
        return $value;
    }
}
