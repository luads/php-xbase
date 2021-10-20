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
        $bytes = unpack('V', $this->fp->read(4));
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
        while (!$this->fp->eof()) {
            $chunk = $this->fp->read(self::BLOCK_LENGTH_IN_BYTES);

            if ($i = strpos($chunk, $endMarker)) {
                $chunk = substr($chunk, 0, $i);
                $memoLength += strlen($chunk);
                $result .= $chunk;
                break;
            }

            $memoLength += self::BLOCK_LENGTH_IN_BYTES;
            $result .= $chunk;
        }

        $info = $this->guessDataType($result);
        assert(isset($info['type']));
        if (MemoObject::TYPE_TEXT === $info['type']) {
            if (chr(0x00) === substr($result, -1)) {
                $result = substr($result, 0, -1); // remove endline symbol (0x00)
            }
            if ($this->table->options['encoding']) {
                $result = $this->encoder->encode($result, $this->table->options['encoding'], 'utf-8');
            }
        }

        return new MemoObject($result, $info['type'], $pointer, $memoLength, $info);
    }

    protected function toBinaryString(string $data, int $lengthInBlocks): string
    {
        return str_pad($data.$this->getBlockEndMarker(), $lengthInBlocks * $this->getBlockLengthInBytes(), chr(0x00));
    }

    protected function calculateBlockCount(string $data): int
    {
        return (int) ceil((strlen($data) + strlen($this->getBlockEndMarker())) / self::BLOCK_LENGTH_IN_BYTES);
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
