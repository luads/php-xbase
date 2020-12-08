<?php declare(strict_types=1);

namespace XBase;

/**
 * Used to store and merge memo deleted area.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @internal
 */
final class BlocksMerger
{
    private $blocksToDelete = [];

    public function add(int $pointer, int $length): self
    {
        $this->blocksToDelete[] = [$pointer, $length];

        return $this;
    }

    public function clear(): void
    {
        $this->blocksToDelete = [];
    }

    public function get(): array
    {
        return $this->squeeze();
    }

    /**
     * Combines several adjacent blocks into one.
     */
    private function squeeze(): array
    {
        $pointers = array_column($this->blocksToDelete, 0);
        array_multisort($pointers, SORT_ASC, $this->blocksToDelete);

        $result = [];
        $i = null;
        $nextPointer = null;
        foreach ($this->blocksToDelete as $arr) {
            [$pointer, $length] = $arr;
            if ($pointer < $nextPointer) {
                continue;
            } elseif ($nextPointer === $pointer) {
                $result[$i] += $length;
            } else {
                $i = $pointer;
                $result[$pointer] = $length;
            }
            $nextPointer = $pointer + $length;
        }

        return $result;
    }

    public function isEmpty(): bool
    {
        return empty($this->blocksToDelete);
    }
}
