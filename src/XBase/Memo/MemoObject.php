<?php

namespace XBase\Memo;

class MemoObject
{
    const TYPE_TEXT  = 1;
    const TYPE_IMAGE = 2;

    /** @var int */
    private $pointer;
    /** @var int|null */
    private $length;
    /** @var int */
    private $type;
    /** @var string */
    private $data;
    /** @var bool */
    private $edited = false;

    /**
     * MemoObject constructor.
     */
    public function __construct(int $pointer, int $length, int $type, string $data)
    {
        $this->pointer = $pointer;
        $this->length = $length;
        $this->type = $type;
        $this->data = $data;
    }

    public function getPointer(): int
    {
        return $this->pointer;
    }

    /**
     * @return int
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;
        $this->edited = true;
        $this->length = null;

        return $this;
    }

    public function __toString()
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function isEdited(): bool
    {
        return $this->edited;
    }
}
