<?php declare(strict_types=1);

namespace XBase\Tests\Record\DataConverter;

use PHPUnit\Framework\TestCase;
use XBase\Column\ColumnInterface;
use XBase\DataConverter\Record\DBaseDataConverter;
use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Memo\MemoInterface;
use XBase\Memo\MemoObject;
use XBase\Record\DBaseRecord;
use XBase\Table;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\DataConverter\Record\DBaseDataConverter
 */
class DBaseDataConverterTest extends TestCase
{
    public function testFromBinaryString(): void
    {
        $base64RowData = 'IEdyb290ICAgICAgICAgICAgICAgMTk2MDExMDFGICAgICAgICAgMSAgICAgICAgICAgICAxMi4xMjM1ICAgICAgICAgNA==';

        /** @var ColumnInterface[] $columns */
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

//        $memo = $this->createMock(MemoInterface::class);
//        $memo
//            ->expects(self::atLeastOnce())
//            ->method('get')
//            ->willReturnMap([
//                ['         1', new MemoObject('memo text', MemoObject::TYPE_TEXT, 1, 1)],
//                ['         4', new MemoObject('memo_image_data', MemoObject::TYPE_IMAGE, 4, 1)],
//            ]);

        $table = $this->createMock(Table::class);
        $table
            ->expects(self::atLeastOnce())
            ->method('getVersion')
            ->willReturn(TableType::DBASE_III_PLUS_MEMO);
        $table
            ->expects(self::atLeastOnce())
            ->method('getColumns')
            ->willReturn($columns);
//        $table
//            ->expects(self::atLeastOnce())
//            ->method('getColumn')
//            ->willReturnCallback(static function (string $name) use ($columns): ?ColumnInterface {
//                foreach ($columns as $c) {
//                    if ($name === $c->getName()) {
//                        return $c;
//                    }
//                }
//
//                return null;
//            });
        $table
            ->expects(self::atLeastOnce())
            ->method('getConvertFrom')
            ->willReturn('cp866');
        $table
            ->expects(self::atLeastOnce())
            ->method('getRecordByteLength')
            ->willReturn(70);
//        $table
//            ->expects(self::atLeastOnce())
//            ->method('getMemo')
//            ->willReturn($memo);

        $converter = new DBaseDataConverter($table);
        $rawData = base64_decode($base64RowData);
        $array = $converter->fromBinaryString($rawData);
        self::assertNotEmpty($array);
        self::assertSame(false, $array['deleted']);
        self::assertSame('Groot', $array['data']['name']);
        self::assertSame('19601101', $array['data']['birthday']);
        self::assertSame(false, $array['data']['is_man']);
        self::assertSame(12.1235, $array['data']['money']);
        /** @var MemoObject $memoBio */
        self::assertSame(1, $array['data']['bio']);
        /** @var MemoObject $memoImage */
        self::assertSame(4, $array['data']['image']);
        // opposite force
        $binaryString = $converter->toBinaryString(new DBaseRecord($table, 1, $array));
        self::assertSame($rawData, $binaryString);
    }
}
