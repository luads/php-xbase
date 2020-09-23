<?php

namespace XBase\Memo;

class FoxproMemo extends AbstractMemo
{
    const BLOCK_LENGTH_LENGTH = 4;

    const BLOCK_TYPE_LENGTH = 4;

    /** @var int */
    protected $nextFreeBlock;

    /** @var int */
    protected $blockSize;

    protected function readHeader(): void
    {
        $this->nextFreeBlock = unpack('N', $this->fp->read(4))[1];
        $this->fp->seek(6);
        $this->blockSize = unpack('n', $this->fp->read(2))[1];

        if (filesize($this->filepath) !== $this->nextFreeBlock * $this->blockSize) {
            @trigger_error('Incorrect next_available_block pointer', E_USER_WARNING);
        }
    }

    public static function getExtension(): string
    {
        return 'fpt';
    }

    public function get($pointer): ?MemoObject
    {
        if (!$this->isOpen()) {
            $this->open();
        }

        if (is_string($pointer)) {
            $pointer = (int) ltrim($pointer, ' ');
        }

        if (0 === $pointer) {
            return null;
        }

        $this->fp->seek($pointer * $this->blockSize);
        $type = unpack('N', $this->fp->read(self::BLOCK_TYPE_LENGTH)); //todo

        $memoLength = unpack('N', $this->fp->read(self::BLOCK_LENGTH_LENGTH));
        $result = $this->fp->read($memoLength[1]);

        $type = $this->guessDataType($result);
        if ($this->convertFrom) {
            $result = iconv($this->convertFrom, 'utf-8', $result);
        }

        return new MemoObject($result, $type, $pointer, $memoLength[1]);
    }

    public function persist(MemoObject $memoObject): MemoObject
    {
        if (null === $memoObject->getPointer()) { //create
            $pointer = $this->nextFreeBlock;
            $this->fp->seek($pointer * $this->blockSize);
            $memoData = $memoObject->getData();
            $this->nextFreeBlock += $length = $this->calculateBlockCount($memoData);
            $this->fp->write($this->toBinaryString($memoData, $length));
            $this->fp->seek(0);
            $this->fp->write(pack('N', $this->nextFreeBlock));

            return new MemoObject($memoData, $this->guessDataType($memoData), $pointer, $length);
        } elseif ($memoObject->isEdited()) { //update
            $newBlockLength = $this->calculateBlockCount($memoObject);
            if ($memoObject->getLength() !== $newBlockLength) {
            }
        }

        return $memoObject;
    }

    protected function calculateBlockCount(string $data): int
    {
        $requiredBytesCount = self::BLOCK_TYPE_LENGTH + self::BLOCK_LENGTH_LENGTH + strlen($data);
        return ceil($requiredBytesCount / $this->blockSize);
    }

    private function toBinaryString(string $data, int $lengthInBlocks): string
    {
        return str_pad(pack('N*', 1, strlen($data)).$data, $lengthInBlocks * $this->blockSize, chr(0x00));
    }
}
