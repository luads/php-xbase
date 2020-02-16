<?php

namespace XBase\Memo;

interface MemoInterface
{
    /**
     * @param $pointer
     *
     * @return MemoObject|null
     */
    public function get($pointer);

    public function open();

    public function close();

    /**
     * @return bool
     */
    public function isOpen();

    /**
     * @return string
     */
    public static function getExtension();
}
