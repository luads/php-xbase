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
     *
     * @param int    $type
     * @param string $data
     */
    public function __construct(int $type, string $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    public function __toString()
    {
        return $this->data;
    }
}
