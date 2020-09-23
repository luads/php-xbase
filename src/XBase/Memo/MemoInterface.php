<?php

namespace XBase\Memo;

interface MemoInterface
{
    /**
     * @param string|int $pointer
     */
    public function get($pointer): ?MemoObject;

    public function persist(MemoObject $memoObject): MemoObject;

    public function open(): void;

    public function close(): void;

    public function isOpen(): bool;

    public static function getExtension(): string;
}
