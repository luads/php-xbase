<?php declare(strict_types=1);

namespace XBase\Tests\DataConverter\Field\DBase;

use PHPUnit\Framework\TestCase;
use XBase\Column\ColumnInterface;
use XBase\DataConverter\Field\DBase\NumberConverter;
use XBase\Table;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\DataConverter\Field\DBase\NumberConverter
 */
class NumberConverterTest extends TestCase
{
    /**
     * Issue #99.
     *
     * @covers ::toBinaryString
     * @dataProvider dataRightDecimalCount
     *
     * @param int|float $in
     */
    public function testRightDecimalCount(int $length, int $decimalCount, $in, string $out)
    {
        $table = $this->createMock(Table::class);
        $column = $this->createMock(ColumnInterface::class);
        $column
            ->method('getLength')
            ->willReturn($length);
        $column
            ->method('getDecimalCount')
            ->willReturn($decimalCount);

        $fieldConverter = new NumberConverter($table, $column);
        self::assertSame($out, $fieldConverter->toBinaryString($in));
    }

    public function dataRightDecimalCount()
    {
        yield [10, 3, null, str_repeat(chr(0x00), 10)];
        yield [10, 0, 10, '        10'];
        yield [10, 3, 10.123, '    10.123'];
        yield [10, 3, 10, '    10.000'];
        yield [10, 3, 1, '     1.000'];
    }
}
