<?php declare(strict_types=1);

namespace XBase\Memo;

class FoxproMemo extends AbstractWritableMemo
{
    const BLOCK_LENGTH_LENGTH = 4;

    const BLOCK_TYPE_LENGTH = 4;

    /** @var int */
    private $blockLengthInBytes;

    protected function readHeader(): void
    {
        $this->fp->seek(0);
        $this->nextFreeBlock = unpack('N', $this->fp->read(4))[1];
        $this->fp->seek(6);
        $this->blockLengthInBytes = unpack('n', $this->fp->read(2))[1];

        if (filesize($this->filepath) !== $this->nextFreeBlock * $this->blockLengthInBytes) {
            @trigger_error('Incorrect next_available_block pointer', E_USER_WARNING);
        }
    }

    protected function writeHeader(): void
    {
        $this->fp->seek(0);
        $this->fp->write(pack('N', $this->nextFreeBlock));
    }

    public static function getExtension(): string
    {
        return 'fpt';
    }

    /**
     * @param int $pointer block address
     */
    public function get(int $pointer): ?MemoObject
    {
        if (!$this->isOpen()) {
            $this->open();
        }

        if (0 === $pointer) {
            return null;
        }

        $this->fp->seek($pointer * $this->blockLengthInBytes);
        $type = unpack('N', $this->fp->read(self::BLOCK_TYPE_LENGTH)); //todo figure out type-enums

        $memoLength = unpack('N', $this->fp->read(self::BLOCK_LENGTH_LENGTH));
        $result = $this->fp->read($memoLength[1]);

        $type = $this->guessDataType($result);
        if ($this->options['encoding']) {
            $result = iconv($this->options['encoding'], 'utf-8', $result);
        }

        return new MemoObject($result, $type, $pointer, $memoLength[1]);
    }

    protected function calculateBlockCount(string $data): int
    {
        $requiredBytesCount = self::BLOCK_TYPE_LENGTH + self::BLOCK_LENGTH_LENGTH + strlen($data);

        return (int) ceil($requiredBytesCount / $this->getBlockLengthInBytes());
    }

    protected function getNextFreeBlock(): int
    {
        return $this->nextFreeBlock;
    }

    protected function getBlockLengthInBytes(): int
    {
        return $this->blockLengthInBytes;
    }
}
