<?php declare(strict_types=1);

namespace XBase\Tests\TableCreator;

trait CleanupTrait
{
    private static $NEW_FILEPATH = __DIR__.'/../Resources/_new.dbf';

    private function cleanupFiles(string $filepath): string
    {
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $memoExts = ['dbt', 'fpt'];
        $fileInfo = pathinfo($filepath);
        foreach ($memoExts as $memoExt) {
            $memoFilepath = $fileInfo['dirname'].DIRECTORY_SEPARATOR.$fileInfo['filename'].$memoExt;
            if (file_exists($memoFilepath)) {
                unlink($memoFilepath);
            }
        }

        return $filepath;
    }
}
