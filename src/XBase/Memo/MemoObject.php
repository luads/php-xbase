<?php declare(strict_types=1);

namespace XBase\Memo;

class MemoObject
{
    const TYPE_TEXT = 1;
    const TYPE_IMAGE = 2;

    /** @var string */
    private $data;
    /** @var int|null */
    private $type;
    /** @var int|null */
    private $pointer;
    /** @var int|null In bytes */
    private $length;

    public function __construct(string $data, ?int $type = null, ?int $pointer = null, ?int $length = null)
    {
        $this->pointer = $pointer;
        $this->length = $length;
        $this->type = $type;
        $this->data = $data;
    }

    public function getPointer(): ?int
    {
        return $this->pointer;
    }

    /**
     * @return int Length in bytes
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function __toString()
    {
        return $this->data;
    }
}
