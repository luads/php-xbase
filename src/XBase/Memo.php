<?php

namespace XBase;

class Memo
{
    protected $fp;
    protected $table;

    public function __construct($table)
    {
      $this->table = $table;
      $this->open();
    }

    protected function open()
    {
        $fileName = preg_replace("dbf", "fpt", $this->table->tableName);

        if (!file_exists($fileName)) {
            return false;
        }

        $this->fp = fopen($fileName, 'rb');

        return $this->fp != false;
    }

    public function get($pointer) {
        $Value = null;
        if($this->fp && $pointer != 0) {
            // Getting block size
            fseek($this->fp, 6);
            $Data = unpack("n", fread($this->fp, 2));
            $Memo_BlockSize = $Data[1];

            fseek($this->fp, $pointer * $Memo_BlockSize);
            $Type = unpack("N", fread($this->fp, 4));
            if($Type[1] == "1") {
                $Len = unpack("N", fread($this->fp, 4));
                $Value = trim(fread($this->fp, $Len[1]));
                if ($this->table->getConvertFrom()) {
                    $Value = iconv($this->table->getConvertFrom(), 'utf-8', $Value);
                }
            } else {
                // Pictures will not be shown
                $Value = "{BINARY_PICTURE}";
            }
        }
        return $Value;
    }
}
