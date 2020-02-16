<?php

namespace XBase\Memo;

class DBase3Memo extends AbstractMemo
{
    const BLOCK_LENGTH = 512;

    public static function getExtension()
    {
        return 'dbt';
    }

    public function get($pointer)
    {
        if (!$this->isOpen()) {
            $this->open();
        }

        fseek($this->fp, $pointer * self::BLOCK_LENGTH);

        $endMarker = chr(0x1A).chr(0x1A).chr(0x00);
        $result = '';
        while (!feof($this->fp)) {
            $result .= fread($this->fp, 1);

            $substr = substr($result, -3);
            if ($endMarker === $substr) {
                $result = substr($result, 0, -3);
                break;
            }
        }

        $type = $this->guessDataType($result);
        if (MemoObject::TYPE_TEXT === $type && chr(0x00) === substr($result, -1)) {
            $result = substr($result, 0, -1); // remove endline symbol (0x00)
        }

        if (MemoObject::TYPE_TEXT === $type && $this->convertFrom) {
            $result = iconv($this->convertFrom, 'utf-8', $result);
        }

        return new MemoObject($type, $result);
    }
}
