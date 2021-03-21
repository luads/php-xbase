<?php declare(strict_types=1);

namespace XBase\Tests\TableReader;

use XBase\Enum\FieldType;
use XBase\Enum\TableFlag;
use XBase\Enum\TableType;
use XBase\Memo\MemoObject;
use XBase\TableReader;
use XBase\Tests\AbstractTestCase;

class DBase7Test extends AbstractTestCase
{
    public function test(): void
    {
        $table = new TableReader(__DIR__.'/../Resources/dBase/dBaseVII.dbf');

        self::assertSame(12, $table->getColumnCount());
        self::assertSame(3, $table->getRecordCount());

        self::assertSame(TableType::DBASE_7_MEMO, $table->getVersion());
//        self::assertSame(Codepage::CP1252, $table->getCodepage()); //todo codepage 0x26
        self::assertSame(false, $table->isFoxpro());
        self::assertSame(645, $table->getHeaderLength());
        self::assertSame(126, $table->getRecordByteLength());
        self::assertSame(false, $table->isInTransaction());
        self::assertSame(false, $table->isEncrypted());
        self::assertSame(TableFlag::NONE, ord($table->getMdxFlag()));

        //<editor-fold desc="columns">
        $columns = $table->getColumns();
        $column = $columns['name'];
        self::assertSame(FieldType::CHAR, $column->getType());
        self::assertSame(1, $column->getBytePos());
        self::assertSame(20, $column->getLength());
        $column = $columns['birthday'];
        self::assertSame(FieldType::DATE, $column->getType());
        self::assertSame(21, $column->getBytePos());
        self::assertSame(8, $column->getLength());
        $column = $columns['is_man'];
        self::assertSame(FieldType::LOGICAL, $column->getType());
        self::assertSame(29, $column->getBytePos());
        self::assertSame(1, $column->getLength());
        $column = $columns['bio'];
        self::assertSame(FieldType::MEMO, $column->getType());
        self::assertSame(30, $column->getBytePos());
        self::assertSame(10, $column->getLength());
        $column = $columns['money'];
        self::assertSame(FieldType::NUMERIC, $column->getType());
        self::assertSame(40, $column->getBytePos());
        self::assertSame(20, $column->getLength());
        self::assertSame(4, $column->getDecimalCount());
        $column = $columns['image'];
        self::assertSame(FieldType::MEMO, $column->getType());
        self::assertSame(60, $column->getBytePos());
        self::assertSame(10, $column->getLength());
        $column = $columns['auto_inc'];
        self::assertSame(FieldType::AUTO_INCREMENT, $column->getType());
        self::assertSame(70, $column->getBytePos());
        self::assertSame(4, $column->getLength());
        $column = $columns['integer'];
        self::assertSame(FieldType::INTEGER, $column->getType());
        self::assertSame(74, $column->getBytePos());
        self::assertSame(4, $column->getLength());
        $column = $columns['large_int'];
        self::assertSame(FieldType::NUMERIC, $column->getType());
        self::assertSame(78, $column->getBytePos());
        self::assertSame(20, $column->getLength());
        $column = $columns['datetime'];
        self::assertSame(FieldType::TIMESTAMP, $column->getType());
        self::assertSame(98, $column->getBytePos());
        self::assertSame(8, $column->getLength());
        $column = $columns['blob'];
        self::assertSame(FieldType::DBASE4_BLOB, $column->getType());
        self::assertSame(106, $column->getBytePos());
        self::assertSame(10, $column->getLength());
        $column = $columns['dbase_ole'];
        self::assertSame(FieldType::GENERAL, $column->getType());
        self::assertSame(116, $column->getBytePos());
        self::assertSame(10, $column->getLength());
        //</editor-fold>

        $this->assertRecords($table);

        $record = $table->moveTo(0);
        self::assertSame(0, $record->get('auto_inc'));
        self::assertSame(1, $record->get('integer'));
        self::assertSame(4.0, $record->get('large_int'));
        self::assertNotEmpty($record->getTimestamp('datetime'));
        self::assertSame('1800-01-01 01:01:01', $record->getDateTimeObject('datetime')->format('Y-m-d H:i:s'));
        self::assertSame('qwe', trim($record->get('blob')));
        self::assertSame(null, $record->get('dbase_ole'));

        $record = $table->nextRecord();
        self::assertSame(1, $record->get('auto_inc'));
        self::assertSame(2, $record->get('integer'));
        self::assertSame(5.0, $record->get('large_int'));
        self::assertSame('1970-01-01 00:00:00', $record->getDateTimeObject('datetime')->format('Y-m-d H:i:s'));
        /** @var MemoObject $memoImg */
        $memoImg = $record->getMemoObject('image');
        self::assertInstanceOf(MemoObject::class, $memoImg);
        self::assertSame(0x3f, $memoImg->getPointer());
        self::assertSame(98034, $memoImg->getLength());
        self::assertSame(MemoObject::TYPE_IMAGE, $memoImg->getType()); //png
        self::assertSame($memoImg->getLength(), strlen($memoImg->getData())); //png

        $record = $table->nextRecord();
        self::assertSame(2, $record->get('auto_inc'));
        self::assertSame(3, $record->get('integer'));
        self::assertSame(6.0, $record->get('large_int'));
        self::assertNotEmpty($record->getTimestamp('datetime'));
        self::assertSame('2020-02-20 20:20:20', $record->getDateTimeObject('datetime')->format('Y-m-d H:i:s'));
        $memoImg = $record->getMemoObject('image');
        self::assertSame($memoImg->getLength(), strlen($memoImg->getData()));
    }

    public function testTs(): void
    {
        $table = new TableReader(__DIR__.'/../Resources/dBase/dBaseVII_ts.dbf');
        self::assertSame(1, $table->getColumnCount());
        self::assertSame(15, $table->getRecordCount()); //has deleted
        self::assertSame(TableType::DBASE_7_NOMEMO, $table->getVersion());

        self::assertSame('1900-01-01 00:00:00', $table->nextRecord()->getDateTimeObject('ts')->format('Y-m-d H:i:s'));
        self::assertSame('1900-01-02 00:00:00', $table->nextRecord()->getDateTimeObject('ts')->format('Y-m-d H:i:s'));
        self::assertSame('1900-01-03 00:00:00', $table->nextRecord()->getDateTimeObject('ts')->format('Y-m-d H:i:s'));
        self::assertSame('2000-01-01 00:00:00', $table->nextRecord()->getDateTimeObject('ts')->format('Y-m-d H:i:s'));
        self::assertSame('2000-01-02 00:00:00', $table->nextRecord()->getDateTimeObject('ts')->format('Y-m-d H:i:s'));
        self::assertSame('2000-01-03 00:00:00', $table->nextRecord()->getDateTimeObject('ts')->format('Y-m-d H:i:s'));
        self::assertSame('2000-01-04 00:00:00', $table->nextRecord()->getDateTimeObject('ts')->format('Y-m-d H:i:s'));
        self::assertSame('2000-01-05 00:00:00', $table->nextRecord()->getDateTimeObject('ts')->format('Y-m-d H:i:s'));
        self::assertSame('2000-01-10 00:00:00', $table->nextRecord()->getDateTimeObject('ts')->format('Y-m-d H:i:s'));
    }

    public function testInt(): void
    {
        $table = new TableReader(__DIR__.'/../Resources/dBase/dBaseVII_int.dbf');
        self::assertSame(1, $table->getColumnCount());
        self::assertSame(6, $table->getRecordCount());
        self::assertSame(TableType::DBASE_7_NOMEMO, $table->getVersion());

        self::assertSame(1, $table->nextRecord()->get('int'));
        self::assertSame(-1, $table->nextRecord()->get('int'));
        self::assertSame(5000000, $table->nextRecord()->get('int'));
        self::assertSame(-5000000, $table->nextRecord()->get('int'));
        self::assertSame(2147483647, $table->nextRecord()->get('int'));
        self::assertSame(-2147483647, $table->nextRecord()->get('int'));
    }

    public function testDouble(): void
    {
        $table = new TableReader(__DIR__.'/../Resources/dBase/dBaseVII_double.dbf');
        self::assertSame(1, $table->getColumnCount());
        self::assertSame(5, $table->getRecordCount());
        self::assertSame(TableType::DBASE_7_NOMEMO, $table->getVersion());

        self::assertSame(-199.99, $table->nextRecord()->get('double'));
        self::assertSame(-74.62, $table->nextRecord()->get('double'));
        self::assertSame(43.65, $table->nextRecord()->get('double'));
        self::assertSame(150.48, $table->nextRecord()->get('double'));
        self::assertSame(0.0, $table->nextRecord()->get('double'));
    }
}
