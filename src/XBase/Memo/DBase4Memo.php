<?php

namespace XBase\Memo;

class DBase4Memo extends AbstractMemo
{
    const BLOCK_SIGN          = 0xFFFF0800;
    const BLOCK_SIGN_LENGTH   = 4;
    const BLOCK_LENGTH_LENGTH = 4;

    /** @var int */
    protected $blockSize;
    /** @var int */
    protected $blockLength;

    protected function readHeader()
    {
        fseek($this->fp, 4);
        $bytes = unpack('N', fread($this->fp, 4));
        $this->blockSize = $bytes[1];

        fseek($this->fp, 20);
        $bytes = unpack('S', fread($this->fp, 2));
        $this->blockLength = $bytes[1];
    }

    public function get(string $pointer): ?MemoObject
    {
        if (!$this->isOpen()) {
            $this->open();
        }

        if (is_string($pointer)) {
            $pointer = (int) ltrim($pointer, ' ');
        }
        fseek($this->fp, $pointer * $this->blockLength);
        $sign = unpack('N', fread($this->fp, self::BLOCK_SIGN_LENGTH));
        if (self::BLOCK_SIGN !== $sign[1]) {
            throw new \LogicException('Wrong dBaseIV block sign/');
        }

        $memoLength = unpack('L', fread($this->fp, self::BLOCK_LENGTH_LENGTH));
        $result = fread($this->fp, $memoLength[1] - self::BLOCK_SIGN_LENGTH - self::BLOCK_LENGTH_LENGTH);

        $type = $this->guessDataType($result);
        if (MemoObject::TYPE_TEXT === $type && $this->convertFrom) {
            $result = iconv($this->convertFrom, 'utf-8', $result);
        }

        return new MemoObject($pointer, $memoLength[1], $type, $result);
    }
}
