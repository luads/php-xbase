<?php declare(strict_types=1);

namespace XBase\Tests\Record\DataConverter\Field\VisualFoxpro;

use PHPUnit\Framework\TestCase;
use XBase\Column\ColumnInterface;
use XBase\DataConverter\Field\VisualFoxpro\DateTimeConverter;
use XBase\Table;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\DataConverter\Field\VisualFoxpro\DateTimeConverter
 */
class DateTimeConverterTest extends TestCase
{
    /**
     * @covers ::toBinaryString
     * @covers ::fromBinaryString
     * @dataProvider dataProvider
     */
    public function test(string $dtSting): void
    {
        $table = $this->createMock(Table::class);
        $column = $this->createMock(ColumnInterface::class);

        $converter = new DateTimeConverter($table, $column);
        $binaryString = $converter->toBinaryString(\DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $dtSting));
        $dt = $converter->fromBinaryString($binaryString);
        self::assertInstanceOf(\DateTimeInterface::class, $dt);
        self::assertSame($dtSting, $dt->format('Y-m-d\TH:i:s.uP'));
    }

    public function dataProvider()
    {
        yield ['1800-01-01T01:01:01.000000+00:00'];
        yield ['1917-11-07T23:10:10.999000+00:00'];
        yield ['1970-01-01T00:00:00.000000+00:00'];
        yield ['2020-02-20T20:20:20.000000+00:00'];
        yield ['2020-11-06T10:10:10.123000+00:00'];
    }

    /**
     * Must return null.
     *
     * @covers ::fromBinaryString
     * @dataProvider dataFromBinaryStringNull
     */
    public function testFromBinaryStringNull(string $base64Value): void
    {
        $table = $this->createMock(Table::class);
        $column = $this->createMock(ColumnInterface::class);

        $converter = new DateTimeConverter($table, $column);
        self::assertNull($converter->fromBinaryString(base64_decode($base64Value)));
    }

    public function dataFromBinaryStringNull()
    {
        yield ['AAAAAAAAAAA='];
        yield [''];
    }
}
