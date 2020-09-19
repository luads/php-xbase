<?php

namespace XBase\Memo;

interface MemoInterface
{
    public function get(string $pointer): ?MemoObject;

    public function open(): void;

    public function close(): void;

    public function isOpen(): bool;

    public static function getExtension(): string;
}
