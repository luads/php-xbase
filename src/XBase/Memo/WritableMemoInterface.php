<?php declare(strict_types=1);

namespace XBase\Memo;

interface WritableMemoInterface extends MemoInterface
{
    /**
     * @return int Block pointer
     */
    public function create(string $data): int;

    /**
     * @return int The number of blocks by which the length has changed
     */
    public function update(int $pointer, string $data): int;

    /**
     * @return int The number of blocks by which the length has changed
     */
    public function delete(int $pointer): void;

    public function save(): void;
}
