<?php declare(strict_types=1);

namespace XBase\Tests\Record\DataConverter\Field\DBase7;

use PHPUnit\Framework\TestCase;
use XBase\DataConverter\Encoder\IconvEncoder;
use XBase\DataConverter\Field\DBase7\DoubleConverter;
use XBase\Header\Column;
use XBase\Table\Table;

class DoubleConverterTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function test(string $binaryString, float $float): void
    {
        $table = new Table();
        $column = new Column();
        $converter = new DoubleConverter($table, $column, new IconvEncoder());
        self::assertSame($float, $converter->fromBinaryString($binaryString));
        self::assertEquals($float, unpack('E', $converter->toBinaryString($float))[1]);
    }

    public function dataProvider()
    {
        yield [
            base64_decode('P5cDMzMzMzI='),
            -199.9,
        ];

        yield [
            base64_decode('P61f//////8='),
            -74.5,
        ];

        yield [
            base64_decode('gAAAAAAAAAA='),
            0.0,
        ];

        yield [
            base64_decode('wCIAAAAAAAA='),
            9.0,
        ];

        yield [
            base64_decode('wFOMzMzMzM0='),
            78.2,
        ];
    }
}
