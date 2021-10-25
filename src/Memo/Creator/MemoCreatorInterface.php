<?php declare(strict_types=1);

namespace XBase\Memo\Creator;

interface MemoCreatorInterface
{
    /**
     * @return string File extension
     */
    public static function getExtension(): string;

    /**
     * Creates memo file on disk with filled header.
     *
     * @return string Path to file
     */
    public function createFile(): string;
}
