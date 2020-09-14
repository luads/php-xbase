<?php declare(strict_types=1);

namespace XBase\Header;

class VisualFoxproHeader extends AbstractHeader
{
    /** @var string */
    private $backlist;

    public function getBacklist(): string
    {
        return $this->backlist;
    }

    public function setBacklist(string $backlist): void
    {
        $this->backlist = $backlist;
    }
}
