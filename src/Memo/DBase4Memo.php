<?php declare(strict_types=1);

namespace XBase\Memo;

class DBase4Memo extends AbstractWritableMemo
{
    const BLOCK_SIGN = 0xFFFF0800;
    const BLOCK_SIGN_LENGTH = 4;
    const BLOCK_LENGTH_LENGTH = 4;

    /** @var int */
    protected $sizeOfBlock;

    /** @var string */
    private $name;

    /** @var int */
    protected $blockLengthInBytes;

    protected function readHeader(): void
    {
        $this->fp->seek(0);
        $this->nextFreeBlock = $this->fp->readUInt();

        $this->sizeOfBlock = unpack('N', $this->fp->read(4))[1];
        $this->name = $this->fp->read(8); //dBaseII

        $this->fp->seek(20);
        $this->blockLengthInBytes = unpack('S', $this->fp->read(2))[1];
    }

    public function get(int $pointer): ?MemoObject
    {
        if (!$this->isOpen()) {
            $this->open();
        }

        $this->fp->seek($pointer * $this->blockLengthInBytes);
        $sign = unpack('N', $this->fp->read(self::BLOCK_SIGN_LENGTH));
        if (self::BLOCK_SIGN !== $sign[1]) {
            throw new \LogicException('Wrong dBaseIV block sign');
        }

        $memoLength = unpack('L', $this->fp->read(self::BLOCK_LENGTH_LENGTH));
        $result = $this->fp->read($memoLength[1]);
//        $result = $this->fp->read($memoLength[1] - self::BLOCK_SIGN_LENGTH - self::BLOCK_LENGTH_LENGTH);

        $info = $this->guessDataType($result);
        assert(isset($info['type']));
        if (MemoObject::TYPE_TEXT === $info['type'] && $this->table->options['encoding']) {
            $result = $this->encoder->encode($result, $this->table->options['encoding'], 'utf-8');
        }

        return new MemoObject($result, $info['type'], $pointer, $memoLength[1]);
    }

    protected function getBlockLengthInBytes(): int
    {
        return $this->blockLengthInBytes;
    }

    protected function calculateBlockCount(string $data): int
    {
        $requiredBytesCount = self::BLOCK_SIGN_LENGTH + self::BLOCK_LENGTH_LENGTH + strlen($data);

        return (int) ceil($requiredBytesCount / $this->getBlockLengthInBytes());
    }

    protected function toBinaryString(string $data, int $lengthInBlocks): string
    {
        $value = pack('N', self::BLOCK_SIGN).pack('L', strlen($data));

        return str_pad($value.$data, $lengthInBlocks * $this->getBlockLengthInBytes(), chr(0));
    }
}
