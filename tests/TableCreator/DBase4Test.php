<?php declare(strict_types=1);

namespace XBase\Tests\TableCreator;

use PHPUnit\Framework\TestCase;

class DBase4Test extends TestCase
{
    use CleanupTrait;

    private static $NEW_FILEPATH = __DIR__.'/Resources/_new.dbf';
}