<?php declare(strict_types=1);

namespace XBase\Tests\Enum;

use PHPUnit\Framework\TestCase;
use XBase\Enum\FieldType;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\Enum\FieldType
 */
class FieldTypeTest extends TestCase
{
    /**
     * @covers ::has
     * @dataProvider dataAll
     */
    public function testAll(string $type, bool $isExists = true)
    {
        self::assertSame($isExists, FieldType::has($type));
    }

    public function dataAll()
    {
        yield ['M'];
        yield ['C'];
        yield ['B'];
        yield ['N'];
        yield ['F'];
        yield ['D'];
    }
}
