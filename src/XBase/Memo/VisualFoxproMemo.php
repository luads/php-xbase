<?php

namespace XBase\Memo;

class VisualFoxproMemo extends FoxproMemo
{
    public function get($pointer): ?MemoObject
    {
        if (is_string($pointer)) {
            $pointer = unpack('l', $pointer)[1];
        }

        return parent::get($pointer);
    }
}
