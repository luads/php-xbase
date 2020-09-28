<?php declare(strict_types=1);

namespace XBase\Memo;

use XBase\BlocksMerger;
use XBase\Stream\Stream;
use XBase\Table;
use XBase\Traits\CloneTrait;

abstract class AbstractWritableMemo extends AbstractMemo implements WritableMemoInterface
{
    use CloneTrait;

    /** @var bool */
    protected $writable = false;

    /**
     * @var BlocksMerger Garbage blocks. Delete blocks while saving.
     */
    protected $blocksToDelete;

    /** @var int */
    protected $nextFreeBlock;

    abstract protected function getBlockSize(): int;

    abstract protected function calculateBlockCount(string $data): int;

    public function __construct(Table $table, string $filepath, ?string $convertFrom = null, bool $writable = false)
    {
        $this->writable = $writable;
        parent::__construct($table, $filepath, $convertFrom);
    }

    public function open(): void
    {
        if (!$this->writable) {
            parent::open();
            return;
        }

        $this->clone();
        $this->fp = Stream::createFromFile($this->cloneFilepath, 'rb+');
        $this->blocksToDelete = new BlocksMerger();
    }

    protected function readHeader(): void
    {
        $stat = $this->fp->stat();
        $this->nextFreeBlock = $stat['size'] - 1;
    }

    public function close(): void
    {
        parent::close();
        if ($this->writable && $this->cloneFilepath) {
            unlink($this->cloneFilepath);
            $this->cloneFilepath = null;
        }
    }

    public function create(string $data): int
    {
        $pointer = $this->nextFreeBlock;
        //write record
        $length = $this->calculateBlockCount($data);
        $this->fp->seek($pointer * $this->getBlockSize());
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

    private function toBinaryString(string $data, int $lengthInBlocks): string
    {
        return str_pad(pack('N*', 1, strlen($data)).$data, $lengthInBlocks * $this->getBlockSize(), chr(0x00));
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

        if (method_exists($this->table, 'onMemoBlocksDelete')) {
            $this->table->onMemoBlocksDelete($blocks);
        }
    }

    private function shiftRecords(int $fromPointer, int $offset): void
    {
        $allPointers = $this->getAllPointers($fromPointer);
        $blockSize = $this->getBlockSize();

        foreach ($allPointers as $p => $size) {
            $this->fp->seek($p * $blockSize);
            // copy record
            $byteLength = $size * $blockSize;
            $binaryData = $this->fp->read($byteLength);
            $pointer = $p - $offset;
            $this->fp->seek($pointer * $blockSize);
            $this->fp->write($binaryData);

            $this->nextFreeBlock = $pointer + $size;
        }

        $this->fp->truncate($this->nextFreeBlock * $blockSize);
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
