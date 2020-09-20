<?php

namespace XBase\Memo;

class DBase3Memo extends AbstractMemo
{
    const BLOCK_LENGTH = 512;

    public function get(string $pointer): ?MemoObject
    {
        if (!$this->isOpen()) {
            $this->open();
        }

        if (is_string($pointer)) {
            $pointer = (int) ltrim($pointer, ' ');
        }
        fseek($this->fp, $pointer * self::BLOCK_LENGTH);

        $endMarker = chr(0x1A).chr(0x1A).chr(0x00);
        $result = '';
        $memoLength = 0;
        while (!feof($this->fp)) {
            $memoLength++;
            $result .= fread($this->fp, 1);

            $substr = substr($result, -3);
            if ($endMarker === $substr) {
                $result = substr($result, 0, -3);
                break;
            }
        }

        $type = $this->guessDataType($result);
        if (MemoObject::TYPE_TEXT === $type) {
            if (chr(0x00) === substr($result, -1)) {
                $result = substr($result, 0, -1); // remove endline symbol (0x00)
            }
            if ($this->convertFrom) {
                $result = iconv($this->convertFrom, 'utf-8', $result);
            }
        }

        return new MemoObject($pointer, $memoLength, $type, $result);
    }
}
