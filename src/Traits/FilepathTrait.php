<?php declare(strict_types=1);

namespace XBase\Traits;

trait FilepathTrait
{
    /**
     * Check and return case insensitive file path if available
     */
    protected static function resolveFilepath(string $filepath)
    {
        if (false !== file_exists($filepath)) {
            return $filepath;
        }

        $lowerpath = strtolower($filepath);
        foreach (glob(dirname($filepath).DIRECTORY_SEPARATOR.'*') as $file) {
            if (strtolower($file) === $lowerpath) {
                return $file;
            }
        }

        return false;
    }
}