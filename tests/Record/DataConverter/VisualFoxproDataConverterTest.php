<?php declare(strict_types=1);

namespace XBase\Tests\Record\DataConverter;

use PHPUnit\Framework\TestCase;
use XBase\Column\ColumnInterface;
use XBase\DataConverter\Record\VisualFoxproDataConverter;
use XBase\Enum\FieldType;
use XBase\Record\VisualFoxproRecord;
use XBase\Table;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\DataConverter\Record\VisualFoxproDataConverter
 */
class VisualFoxproDataConverterTest extends TestCase
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
        $c->method('getLength')->willReturn(4);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('money');
        $c->method('getType')->willReturn(FieldType::NUMERIC);
        $c->method('getBytePos')->willReturn(34);
        $c->method('getLength')->willReturn(20);
        $c->method('getDecimalCount')->willReturn(4);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('image');
        $c->method('getType')->willReturn(FieldType::MEMO);
        $c->method('getBytePos')->willReturn(54);
        $c->method('getLength')->willReturn(4);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('rate');
        $c->method('getType')->willReturn(FieldType::FLOAT);
        $c->method('getBytePos')->willReturn(58);
        $c->method('getLength')->willReturn(10);
        $c->method('getDecimalCount')->willReturn(2);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('general');
        $c->method('getType')->willReturn(FieldType::GENERAL);
        $c->method('getBytePos')->willReturn(68);
        $c->method('getLength')->willReturn(4);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('blob');
        $c->method('getType')->willReturn(FieldType::BLOB);
        $c->method('getBytePos')->willReturn(72);
        $c->method('getLength')->willReturn(4);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('currency');
        $c->method('getType')->willReturn(FieldType::CURRENCY);
        $c->method('getBytePos')->willReturn(76);
        $c->method('getLength')->willReturn(8);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('datetime');
        $c->method('getType')->willReturn(FieldType::DATETIME);
        $c->method('getBytePos')->willReturn(84);
        $c->method('getLength')->willReturn(8);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('double');
        $c->method('getType')->willReturn(FieldType::DOUBLE);
        $c->method('getBytePos')->willReturn(92);
        $c->method('getLength')->willReturn(8);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('integer');
        $c->method('getType')->willReturn(FieldType::INTEGER);
        $c->method('getBytePos')->willReturn(100);
        $c->method('getLength')->willReturn(4);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('ai');
        $c->method('getType')->willReturn(FieldType::INTEGER);
        $c->method('getBytePos')->willReturn(104);
        $c->method('getLength')->willReturn(4);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('varchar');
        $c->method('getType')->willReturn(FieldType::VAR_FIELD);
        $c->method('getBytePos')->willReturn(108);
        $c->method('getLength')->willReturn(10);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('name_bin');
        $c->method('getType')->willReturn(FieldType::CHAR);
        $c->method('getBytePos')->willReturn(118);
        $c->method('getLength')->willReturn(20);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('bio_bin');
        $c->method('getType')->willReturn(FieldType::MEMO);
        $c->method('getBytePos')->willReturn(138);
        $c->method('getLength')->willReturn(4);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('varbinary');
        $c->method('getType')->willReturn(FieldType::VARBINARY);
        $c->method('getBytePos')->willReturn(142);
        $c->method('getLength')->willReturn(10);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('varchar_bi');
        $c->method('getType')->willReturn(FieldType::VAR_FIELD);
        $c->method('getBytePos')->willReturn(152);
        $c->method('getLength')->willReturn(10);

        $columns[] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('_nullflags');
        $c->method('getType')->willReturn(FieldType::IGNORE);
        $c->method('getBytePos')->willReturn(162);
        $c->method('getLength')->willReturn(2);
        //</editor-fold>

        $table = $this->createMock(Table::class);
        $table
            ->expects(self::atLeastOnce())
            ->method('getColumns')
            ->willReturn($columns);
        $table
            ->expects(self::atLeastOnce())
            ->method('getConvertFrom')
            ->willReturn('cp866');
        $table
            ->expects(self::atLeastOnce())
            ->method('getRecordByteLength')
            ->willReturn(164);

        $converter = new VisualFoxproDataConverter($table);
        $array = $converter->fromBinaryString($rawData);
        $binaryString = $converter->toBinaryString(new VisualFoxproRecord($table, 1, $array));
        self::assertSame($rawData, $binaryString);
    }

    public function dataProvider()
    {
        yield [
            base64_decode('IEdyb290ICAgICAgICAgICAgICAgMTk2MDExMDFGthMAACAgICAgICAgICAgICAxMi4xMjM1IAAAACAgICAgIDEuMjABAAAARhMAAOAuAAAAAAAAAUskAMjcNwBmZmZmZmYCQAAAAAABAAAAcXdlAAAAAAAAA0dyb290ICAgICAgICAgICAgICAgzhMAAKvN7wAAAAAAAANxd2UAAAAAAAADIAo='),
        ];
    }
}
