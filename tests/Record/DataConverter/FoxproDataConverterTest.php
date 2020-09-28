<?php declare(strict_types=1);

namespace XBase\Tests\Record\DataConverter;

use PHPUnit\Framework\TestCase;
use XBase\Column\ColumnInterface;
use XBase\DataConverter\Record\FoxproDataConverter;
use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Record\FoxproRecord;
use XBase\Table;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\DataConverter\Record\FoxproDataConverter
 */
class FoxproDataConverterTest extends TestCase
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
        $c->method('getName')->willReturn('rate');
        $c->method('getType')->willReturn(FieldType::FLOAT);
        $c->method('getBytePos')->willReturn(70);
        $c->method('getLength')->willReturn(10);
        $c->method('getDecimalCount')->willReturn(2);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('general');
        $c->method('getType')->willReturn(FieldType::GENERAL);
        $c->method('getBytePos')->willReturn(80);
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
//        $table
//            ->expects(self::atLeastOnce())
//            ->method('getConvertFrom')
//            ->willReturn('cp866');
        $table
            ->expects(self::atLeastOnce())
            ->method('getRecordByteLength')
            ->willReturn(90);

        $converter = new FoxproDataConverter($table);
        $array = $converter->fromBinaryString($rawData);
        $binaryString = $converter->toBinaryString(new FoxproRecord($table, 1, $array));
        self::assertSame($rawData, $binaryString);
    }

    public function dataProvider()
    {
        yield [
            base64_decode('IEdyb290ICAgICAgICAgICAgICAgMTk2MDExMDFGICAgICAgICAgOCAgICAgICAgICAgICAxMi4xMjM1ICAgICAgICAzMiAgICAgIDEuMjAgICAgICAgNDU5'),
        ];
    }
}
