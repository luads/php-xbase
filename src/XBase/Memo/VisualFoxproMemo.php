<?php

namespace XBase\Memo;

class VisualFoxproMemo extends FoxproMemo
{
    /**
     * @inheritDoc
     */
    public function get(string $pointer): ?MemoObject
    {
        $decPointer = unpack('l', $pointer)[1];
        return parent::get($decPointer);
    }
}
