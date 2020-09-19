<?php

namespace XBase\Memo;

class MemoObject
{
    const TYPE_TEXT  = 1;
    const TYPE_IMAGE = 2;

    /** @var int */
    protected $type;
    /** @var string */
    protected $data;

    /**
     * MemoObject constructor.
     */
    public function __construct(int $type, string $data)
    {
        $this->type = $type;
        $this->data = $data;
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

        return $this;
    }

    public function __toString()
    {
        return $this->data;
    }
}
