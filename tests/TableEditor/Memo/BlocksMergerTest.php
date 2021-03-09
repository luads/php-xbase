<?php declare(strict_types=1);

namespace XBase\Tests\TableEditor\Memo;

use PHPUnit\Framework\TestCase;
use XBase\BlocksMerger;

class BlocksMergerTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testGet(array $add, array $expected): void
    {
        $gc = new BlocksMerger();
        foreach ($add as $arr) {
            [$a, $b] = $arr;
            $gc->add($a, $b);
        }
        self::assertEquals($expected, $gc->get());
    }

    public function dataProvider()
    {
        yield [
            [
                [200, 50],
                [100, 10],
                [180, 1],
                [110, 20],
                [130, 40],
            ],
            [
                100 => 70,
                180 => 1,
                200 => 50,
            ],
        ];

        yield [
            [
                [1, 100],
                [5, 10],
                [50, 60],
            ],
            [
                1 => 100,
            ],
        ];
    }
}
