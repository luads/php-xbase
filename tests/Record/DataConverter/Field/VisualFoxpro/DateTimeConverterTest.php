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
     * @covers ::fromBinaryString
     * @dataProvider dataFromBinaryString
     */
    public function testFromBinaryString(string $base64Value, string $expectedDateSting): void
    {
        $table = $this->createMock(Table::class);
        $column = $this->createMock(ColumnInterface::class);

        $converter = new DateTimeConverter($table, $column);
        $dt = $converter->fromBinaryString(base64_decode($base64Value));
        self::assertInstanceOf(\DateTimeInterface::class, $dt);
        self::assertSame($expectedDateSting, $dt->format(DATE_ATOM));
    }

    /**
     * @covers ::toBinaryString
     * @dataProvider dataFromBinaryString
     */
    public function testToBinaryString(string $base64Value, string $dateSting): void
    {
        $table = $this->createMock(Table::class);
        $column = $this->createMock(ColumnInterface::class);

        $converter = new DateTimeConverter($table, $column);
        $string = $converter->toBinaryString(\DateTime::createFromFormat(DATE_ATOM, $dateSting));
        self::assertSame($base64Value, base64_encode($string));
    }

    public function dataFromBinaryString()
    {
        yield ['AUskAMjcNwA=', '1800-01-01T01:01:01+00:00'];
        yield ['jD0lAAAAAAA=', '1970-01-01T00:00:00+00:00'];
        yield ['FIUlAKA/XQQ=', '2020-02-20T20:20:20+00:00'];
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
