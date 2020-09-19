<?php

namespace XBase\Memo;

class FoxproMemo extends AbstractMemo
{
    const BLOCK_LENGTH_LENGTH = 4;

    /** @var int */
    protected $blockSize;

    protected function readHeader()
    {
        fseek($this->fp, 6);
        $bytes = unpack('n', fread($this->fp, 2));
        $this->blockSize = $bytes[1];
    }

    public static function getExtension(): string
    {
        return 'fpt';
    }

    /**
     * @param int $pointer
     *
     * @return false|string|null
     */
    public function get(string $pointer): ?MemoObject
    {
        if (!$this->isOpen()) {
            $this->open();
        }

        if (is_string($pointer)) {
            $pointer = (int) ltrim($pointer, ' ');
        }
        fseek($this->fp, $pointer * $this->blockSize);
        $type = unpack('N', fread($this->fp, 4)); //todo

        $memoLength = unpack('N', fread($this->fp, self::BLOCK_LENGTH_LENGTH));
        $result = fread($this->fp, $memoLength[1]);

        $type = $this->guessDataType($result);
        if ($this->convertFrom) {
            $result = iconv($this->convertFrom, 'utf-8', $result);
        }

        return new MemoObject($type, $result);
    }
}
