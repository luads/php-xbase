<?php declare(strict_types=1);

namespace XBase\Tests\Record\DataConverter;

use PHPUnit\Framework\TestCase;
use XBase\Column\ColumnInterface;
use XBase\DataConverter\Record\DBase7DataConverter;
use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Record\DBase7Record;
use XBase\Table;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\DataConverter\Record\DBase7DataConverter
 */
class DBase7DataConverterTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function test(string $rawData): void
    {
        //<editor-fold desc="columns">
        $columns = [];

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('name');
        $c->method('getType')->willReturn(FieldType::CHAR);
        $c->method('getBytePos')->willReturn(1);
        $c->method('getLength')->willReturn(20);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('birthday');
        $c->method('getType')->willReturn(FieldType::DATE);
        $c->method('getBytePos')->willReturn(21);
        $c->method('getLength')->willReturn(8);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('is_man');
        $c->method('getType')->willReturn(FieldType::LOGICAL);
        $c->method('getBytePos')->willReturn(29);
        $c->method('getLength')->willReturn(1);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('bio');
        $c->method('getType')->willReturn(FieldType::MEMO);
        $c->method('getBytePos')->willReturn(30);
        $c->method('getLength')->willReturn(10);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('money');
        $c->method('getType')->willReturn(FieldType::NUMERIC);
        $c->method('getBytePos')->willReturn(40);
        $c->method('getLength')->willReturn(20);
        $c->method('getDecimalCount')->willReturn(4);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('image');
        $c->method('getType')->willReturn(FieldType::MEMO);
        $c->method('getBytePos')->willReturn(60);
        $c->method('getLength')->willReturn(10);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('auto_inc');
        $c->method('getType')->willReturn(FieldType::AUTO_INCREMENT);
        $c->method('getBytePos')->willReturn(70);
        $c->method('getLength')->willReturn(4);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('integer');
        $c->method('getType')->willReturn(FieldType::INTEGER);
        $c->method('getBytePos')->willReturn(74);
        $c->method('getLength')->willReturn(4);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('large_int');
        $c->method('getType')->willReturn(FieldType::NUMERIC);
        $c->method('getBytePos')->willReturn(78);
        $c->method('getLength')->willReturn(20);
        $c->method('getDecimalCount')->willReturn(4);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('datetime');
        $c->method('getType')->willReturn(FieldType::TIMESTAMP);
        $c->method('getBytePos')->willReturn(98);
        $c->method('getLength')->willReturn(8);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('blob');
        $c->method('getType')->willReturn(FieldType::DBASE4_BLOB);
        $c->method('getBytePos')->willReturn(106);
        $c->method('getLength')->willReturn(10);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('dbase_ole');
        $c->method('getType')->willReturn(FieldType::GENERAL);
        $c->method('getBytePos')->willReturn(116);
        $c->method('getLength')->willReturn(10);
        //</editor-fold>

        $table = $this->createMock(Table::class);
        $table
            ->expects(self::atLeastOnce())
            ->method('getVersion')
            ->willReturn(TableType::FOXPRO_MEMO);
        $table
            ->expects(self::atLeastOnce())
            ->method('getColumns')
            ->willReturn($columns);
        $table
            ->expects(self::atLeastOnce())
            ->method('getRecordByteLength')
            ->willReturn(126);

        $converter = new DBase7DataConverter($table);
        $array = $converter->fromBinaryString($rawData);
        $binaryString = $converter->toBinaryString(new DBase7Record($table, 1, $array));
        self::assertSame($rawData, $binaryString);
    }

    public function dataProvider()
    {
        yield [
            base64_decode('IEdyb290ICAgICAgICAgICAgICAgMTk2MDExMDFGMDAwMDAwMDAwMSAgICAgICAgICAgICAxMi4xMjM1MDAwMDAwMDAwOYAAAACAAAABICAgICAgICAgICAgICAgICAgIDRCydEEX45kADAwMDAwMDA1ODcwMDAwMDAwMDAw'),
        ];
    }
}