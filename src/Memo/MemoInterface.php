<?php declare(strict_types=1);

namespace XBase\Memo;

interface MemoInterface
{
    public function get(int $pointer): ?MemoObject;

    public function open(): void;

    public function close(): void;

    public function isOpen(): bool;

    public static function getExtension(): string;
}
