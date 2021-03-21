<?php declare(strict_types=1);

namespace XBase\Memo\Creator;

interface MemoCreatorInterface
{
    public static function getExtension(): string;

    public function createFile(): string;
}
