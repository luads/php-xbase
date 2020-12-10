<?php declare(strict_types=1);

namespace XBase\Tests\Record;

use PHPUnit\Framework\TestCase;
use XBase\Column\ColumnInterface;
use XBase\DataConverter\Record\DBaseDataConverter;
use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Memo\MemoObject;
use XBase\Memo\WritableMemoInterface;
use XBase\Record\DBaseRecord;
use XBase\Table;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\Record\DBaseRecord
 */
class DBaseRecordTest extends TestCase
{
    /**
     * @covers ::copyFrom
     */
    public function testCopyFrom(): void
    {
        $columns = [];

        $columns['name'] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('name');
        $c->method('getType')->willReturn(FieldType::CHAR);
        $c->method('getBytePos')->willReturn(1);
        $c->method('getLength')->willReturn(20);

        $columns['birthday'] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('birthday');
        $c->method('getType')->willReturn(FieldType::DATE);
        $c->method('getBytePos')->willReturn(21);
        $c->method('getLength')->willReturn(8);

        $columns['is_man'] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('is_man');
        $c->method('getType')->willReturn(FieldType::LOGICAL);
        $c->method('getBytePos')->willReturn(29);
        $c->method('getLength')->willReturn(1);

        $columns['bio'] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('bio');
        $c->method('getType')->willReturn(FieldType::MEMO);
        $c->method('getBytePos')->willReturn(30);
        $c->method('getLength')->willReturn(10);

        $columns['money'] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('money');
        $c->method('getType')->willReturn(FieldType::NUMERIC);
        $c->method('getBytePos')->willReturn(40);
        $c->method('getLength')->willReturn(20);
        $c->method('getDecimalCount')->willReturn(4);

        $columns['image'] = $c = $this->createMock(ColumnInterface::class);
        $c->method('getName')->willReturn('image');
        $c->method('getType')->willReturn(FieldType::MEMO);
        $c->method('getBytePos')->willReturn(60);
        $c->method('getLength')->willReturn(10);

        $memo = $this->createMock(WritableMemoInterface::class);
        $memo
            ->expects(self::atLeastOnce())
            ->method('create')
            ->willReturnMap([
                ['memo text', 1],
                ['memo_image_data', 2],
            ]);
        $memo
            ->expects(self::atLeastOnce())
            ->method('get')
            ->willReturnMap([
                [1, new MemoObject('memo text')],
                [2, new MemoObject('memo_image_data')],
            ]);

        $table = $this->createMock(Table::class);
        $table
            ->expects(self::atLeastOnce())
            ->method('getVersion')
            ->willReturn(TableType::DBASE_III_PLUS_MEMO);
        $table
            ->expects(self::atLeastOnce())
            ->method('getColumns')
            ->willReturn($columns);
        $table
            ->expects(self::atLeastOnce())
            ->method('getRecordByteLength')
            ->willReturn(70);
        $table
            ->expects(self::atLeastOnce())
            ->method('getConvertFrom')
            ->willReturn('cp866');
        $table
            ->expects(self::atLeastOnce())
            ->method('getMemo')
            ->willReturn($memo);
        $table
            ->expects(self::atLeastOnce())
            ->method('getColumn')
            ->willReturnCallback(static function (string $columnName) use ($columns): ColumnInterface {
                return $columns[$columnName];
            });

        $converter = new DBaseDataConverter($table);

        $record1 = new DBaseRecord($table, 1, $converter->fromBinaryString(''));
        $record1
            ->set('name', 'Groot')
            ->set('birthday', \DateTime::createFromFormat('Ymd', '19601101'))
            ->set('is_man', false)
            ->set('money', 12.1235)
            ->set('bio', 'memo text')
            ->set('image', 'memo_image_data');

        $record2 = new DBaseRecord($table, 2);
        $record2->copyFrom($record1);

        self::assertSame($converter->toBinaryString($record1), $converter->toBinaryString($record2));
    }
}
