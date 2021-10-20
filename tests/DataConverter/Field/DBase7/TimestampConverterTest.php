<?php declare(strict_types=1);

namespace XBase\Tests\Record\DataConverter\Field\DBase7;

use PHPUnit\Framework\TestCase;
use XBase\DataConverter\Encoder\IconvEncoder;
use XBase\DataConverter\Field\DBase7\TimestampConverter;
use XBase\Header\Column;
use XBase\Table\Table;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\DataConverter\Field\DBase7\TimestampConverter
 */
class TimestampConverterTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function test(string $binaryString, int $int): void
    {
        $table = new Table();
        $column = new Column();
        $converter = new TimestampConverter($table, $column, new IconvEncoder());
        self::assertSame($int, $converter->fromBinaryString($binaryString));
        self::assertEquals(base64_encode($binaryString), base64_encode($converter->toBinaryString($int)));
    }

    public function dataProvider()
    {
        yield [
            base64_decode('QsxBi6maAAA='),
            0,
        ];

        yield [
            base64_decode('QsxBjjzIAAA='),
            86400,
        ];

        yield [
            base64_decode('QsxBkM/2AAA='),
            172800,
        ];

        yield [
            base64_decode('Qsyvw6SeAAA='),
            946771200,
        ];

        yield [
            base64_decode('QsnRBF+OZAA='),
            -5364658739,
        ];

        yield [
            base64_decode('Qsz5vcqp0AA='),
            1582230020,
        ];

        yield [
            base64_decode('Qsb1805mAAA='),
            -11644473600,
        ];
    }
}
