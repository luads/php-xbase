<?php declare(strict_types=1);

namespace XBase\Tests\Writable;

trait CloneTableTrait
{
    /** @var string[] */
    private $cloneFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->cloneFiles as $filepath) {
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
    }

    private function duplicateFile(string $file): string
    {
        $info = pathinfo($file);
        $newName = uniqid($info['filename'].'_');
        $this->cloneFiles[] = $copyTo = "{$info['dirname']}/$newName.{$info['extension']}";
        self::assertTrue(copy($file, $copyTo));

        $memoExt = ['fpt'];
        foreach ($memoExt as $ext) {
            $memoFile = "{$info['dirname']}/{$info['filename']}.$ext";
            if (file_exists($memoFile)) {
                $this->cloneFiles[] = $memoFileCopy = "{$info['dirname']}/$newName.$ext";
                self::assertTrue(copy($memoFile, $memoFileCopy));
            }
        }

        return $copyTo;
    }
}
