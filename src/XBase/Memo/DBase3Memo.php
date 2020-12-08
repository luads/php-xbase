<?php declare(strict_types=1);

namespace XBase\Memo;

class DBase3Memo extends AbstractWritableMemo
{
    const BLOCK_LENGTH_IN_BYTES = 512;

    /** @var int */
    private $version;

    protected function readHeader(): void
    {
        $this->fp->seek(0);
        $bytes = unpack('N', $this->fp->read(4));
        $this->nextFreeBlock = $bytes[1];

        $this->fp->seek(16);
        $this->version = $this->fp->readChar();
    }

    public function get(int $pointer): ?MemoObject
    {
        if (!$this->isOpen()) {
            $this->open();
        }

        $this->fp->seek($pointer * self::BLOCK_LENGTH_IN_BYTES);

        $endMarker = $this->getBlockEndMarker();
        $result = '';
        $memoLength = 0;
        while (!$this->fp->eof()) { //todo too slow need speedup
            $memoLength++;
            $result .= $this->fp->read(1);

            $substr = substr($result, -3);
            if ($endMarker === $substr) {
                $memoLength -= 3;
                $result = substr($result, 0, -3);
                break;
            }
        }

        $type = $this->guessDataType($result);
        if (MemoObject::TYPE_TEXT === $type) {
            if (chr(0x00) === substr($result, -1)) {
                $result = substr($result, 0, -1); // remove endline symbol (0x00)
            }
            if ($this->options['encoding']) {
                $result = iconv($this->options['encoding'], 'utf-8', $result);
            }
        }

        return new MemoObject($result, $type, $pointer, $memoLength);
    }

    protected function calculateBlockCount(string $data): int
    {
        return (int) ceil(strlen($data) + strlen($this->getBlockEndMarker()) / self::BLOCK_LENGTH_IN_BYTES);
    }

    private function getBlockEndMarker(): string
    {
        return chr(0x1A).chr(0x1A).chr(0x00);
    }

    protected function getBlockLengthInBytes(): int
    {
        return self::BLOCK_LENGTH_IN_BYTES;
    }
}
