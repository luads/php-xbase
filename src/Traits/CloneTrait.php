<?php declare(strict_types=1);

namespace XBase\Traits;

trait CloneTrait
{
    /** @var string|null */
    private $cloneFilepath;

    /**
     * We will perform any edits on clone.
     */
    private function clone(): void
    {
        $info = pathinfo($this->getFilepath());
        $this->cloneFilepath = "{$info['dirname']}/~{$info['basename']}";
        if (!copy($this->getFilepath(), $this->cloneFilepath)) {
            throw new \RuntimeException('Failed to clone original file: '.$this->getFilepath());
        }
    }
}
