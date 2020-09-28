<?php declare(strict_types=1);

namespace XBase\Tests\Record\DataConverter\Field\DBase7;

use PHPUnit\Framework\TestCase;
use XBase\Column\ColumnInterface;
use XBase\DataConverter\Field\DBase7\IntegerConverter;
use XBase\Table;

class IntegerConverterTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function test(string $binaryString, int $int): void
    {
        $table = $this->createMock(Table::class);
        $column = $this->createMock(ColumnInterface::class);
        $converter = new IntegerConverter($table, $column);
        self::assertSame($int, $converter->fromBinaryString($binaryString));
        self::assertEquals(unpack('C*', $binaryString), unpack('C*', $converter->toBinaryString($int)));
    }

    public function dataProvider()
    {
        yield [
            base64_decode('f////w=='),
            -1,
        ];

        yield [
            base64_decode('gAAAAA=='),
            0,
        ];

        yield [
            base64_decode('gAAAAQ=='),
            1,
        ];

        yield [
            base64_decode('gAAAAg=='),
            2,
        ];

        yield [
            base64_decode('gExLQA=='),
            5000000,
        ];

        yield [
            base64_decode('f7O0wA=='),
            -5000000,
        ];

        yield [
            base64_decode('/////w=='),
            2147483647,
        ];

        yield [
            base64_decode('AAAAAQ=='),
            -2147483647,
        ];
    }
}