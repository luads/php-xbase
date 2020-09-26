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

    protected function readHeader(): void
    {
        $this->fp->seek(4);
        $bytes = unpack('N', $this->fp->read(4));
        $this->blockSize = $bytes[1];

        $this->fp->seek(20);
        $bytes = unpack('S', $this->fp->read(2));
        $this->blockLength = $bytes[1];
    }

    public function get(int $pointer): ?MemoObject
    {
        if (!$this->isOpen()) {
            $this->open();
        }

//        if (is_string($pointer)) {
//            $pointer = (int) ltrim($pointer);
//        }

        $this->fp->seek($pointer * $this->blockLength);
        $sign = unpack('N', $this->fp->read(self::BLOCK_SIGN_LENGTH));
        if (self::BLOCK_SIGN !== $sign[1]) {
            throw new \LogicException('Wrong dBaseIV block sign');
        }

        $memoLength = unpack('L', $this->fp->read(self::BLOCK_LENGTH_LENGTH));
        $result = $this->fp->read($memoLength[1] - self::BLOCK_SIGN_LENGTH - self::BLOCK_LENGTH_LENGTH);

        $type = $this->guessDataType($result);
        if (MemoObject::TYPE_TEXT === $type && $this->convertFrom) {
            $result = iconv($this->convertFrom, 'utf-8', $result);
        }

        return new MemoObject($result, $type, $pointer, $memoLength[1]);
    }
}
