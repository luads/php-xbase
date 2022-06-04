<?php

declare(strict_types=1);

namespace XBase\Tests\DataConverter\Field\DBase;

use PHPUnit\Framework\TestCase;
use XBase\DataConverter\Encoder\EncoderInterface;
use XBase\DataConverter\Field\DBase\CharConverter;
use XBase\Header\Column;
use XBase\Table\Table;

/**
 * @coversDefaultClass \XBase\DataConverter\Field\DBase\CharConverter
 */
class CharConverterTest extends TestCase
{
    public function testNullValue(): void
    {
        $table = $this->createMock(Table::class);
        $column = new Column(['length' => 1]);
        $encoder = $this->createMock(EncoderInterface::class);
        $c = new CharConverter($table, $column, $encoder);
        self::assertSame(' ', $c->toBinaryString(null));
    }

    public function testIntValue(): void
    {
        $table = $this->createMock(Table::class);
        $column = new Column(['length' => 1]);
        $encoder = $this->createMock(EncoderInterface::class);
        $c = new CharConverter($table, $column, $encoder);
        self::assertSame('1', $c->toBinaryString(1));
    }
}
