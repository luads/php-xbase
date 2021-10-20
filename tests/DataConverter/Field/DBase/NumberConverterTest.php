<?php declare(strict_types=1);

namespace XBase\Tests\DataConverter\Field\DBase;

use PHPUnit\Framework\TestCase;
use XBase\DataConverter\Encoder\IconvEncoder;
use XBase\DataConverter\Field\DBase\NumberConverter;
use XBase\Header\Column;
use XBase\Table\Table;

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
        $table = new Table();
        $column = new Column();
        $column->length = $length;
        $column->decimalCount = $decimalCount;

        $fieldConverter = new NumberConverter($table, $column, new IconvEncoder());
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
