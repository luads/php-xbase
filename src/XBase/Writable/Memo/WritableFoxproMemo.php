<?php declare(strict_types=1);

namespace XBase\Writable\Memo;

use XBase\Memo\FoxproMemo;
use XBase\Stream\Stream;
use XBase\Writable\CloneTrait;

class WritableFoxproMemo extends FoxproMemo implements WritableMemoInterface
{
    use CloneTrait;

    /**
     * @var BlocksMerger Garbage blocks. Delete blocks while saving.
     */
    protected $blocksToDelete;

    public function open(): void
    {
        $this->clone();
        $this->fp = Stream::createFromFile($this->cloneFilepath, 'rb+');
        $this->blocksToDelete = new BlocksMerger();
    }

    protected function writeHeader(): void
    {
        $this->fp->seek(0);
        $this->fp->write(pack('N', $this->nextFreeBlock));
    }

    public function close(): void
    {
        parent::close();
        if ($this->cloneFilepath) {
            unlink($this->cloneFilepath);
            $this->cloneFilepath = null;
        }
    }

    public function create(string $data): int
    {
        $pointer = $this->nextFreeBlock;
        //write record
        $length = $this->calculateBlockCount($data);
        $this->fp->seek($pointer * $this->blockSize);
        $this->fp->write($this->toBinaryString($data, $length));

        $this->nextFreeBlock += $length;

        return $pointer;
    }

    public function update(int $pointer, string $data): int
    {
        $this->delete($pointer);

        return $this->create($data);
    }

    public function delete(int $pointer): void
    {
        $memoObject = $this->get($pointer);
        $blockLength = $this->calculateBlockCount($memoObject->getData());
        $this->blocksToDelete->add($pointer, $blockLength);
    }

    public function save(): void
    {
        $this->doDelete();
        $this->writeHeader();
        copy($this->cloneFilepath, $this->filepath);
    }

    protected function calculateBlockCount(string $data): int
    {
        $requiredBytesCount = self::BLOCK_TYPE_LENGTH + self::BLOCK_LENGTH_LENGTH + strlen($data);
        return (int) ceil($requiredBytesCount / $this->blockSize);
    }

    private function toBinaryString(string $data, int $lengthInBlocks): string
    {
        return str_pad(pack('N*', 1, strlen($data)).$data, $lengthInBlocks * $this->blockSize, chr(0x00));
    }

    /**
     * Deletes garbage
     */
    private function doDelete(): void
    {
        if ($this->blocksToDelete->isEmpty()) {
            return;
        }

        $blocks = $this->blocksToDelete->get();
        $this->blocksToDelete->clear();
        $shift = 0;
        foreach ($blocks as $pointer => $length) {
            $this->shiftRecords($pointer - $shift + $length, $length);
            $shift += $length;
        }

        $this->table->onMemoBlocksDelete($blocks);
    }

    private function shiftRecords(int $fromPointer, int $offset): void
    {
        $allPointers = $this->getAllPointers($fromPointer);

        foreach ($allPointers as $p => $size) {
            $this->fp->seek($p * $this->blockSize);
            // copy record
            $byteLength = $size * $this->blockSize;
            $binaryData = $this->fp->read($byteLength);
            $pointer = $p - $offset;
            $this->fp->seek($pointer * $this->blockSize);
            $this->fp->write($binaryData);

            $this->nextFreeBlock = $pointer + $size;
        }

        $this->fp->truncate($this->nextFreeBlock * $this->blockSize);
    }

    private function getAllPointers(int $fromPointer): array
    {
        $result = [];
        while ($fromPointer < $this->nextFreeBlock) {
            $memoRecord = $this->get($fromPointer);
            $calculateBlockCount = $this->calculateBlockCount($memoRecord->getData());
            $result[$fromPointer] = $calculateBlockCount;
            $fromPointer += $calculateBlockCount;
        }

        return $result;
    }
}
