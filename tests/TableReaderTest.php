<?php declare(strict_types=1);

namespace XBase\Tests;

use XBase\Column\ColumnInterface;
use XBase\Enum\Codepage;
use XBase\Enum\FieldType;
use XBase\Enum\TableFlag;
use XBase\Enum\TableType;
use XBase\Memo\MemoObject;
use XBase\Record\DBaseRecord;
use XBase\Record\RecordInterface;
use XBase\TableReader;

class TableReaderTest extends AbstractTestCase
{
    public function testRead(): void
    {
        $table = new TableReader(__DIR__.'/Resources/dBase/dBaseIII_nomemo.dbf', ['encoding' => 'cp866']);

        self::assertSame(18, $table->getColumnCount());
        self::assertSame(10, $table->getRecordCount());

        self::assertSame(3, $table->getVersion());
        self::assertSame(TableType::DBASE_III_PLUS_NOMEMO, $table->getVersion());
        self::assertSame(Codepage::UNDEFINED, $table->getCodepage());
        self::assertSame(false, $table->isFoxpro());
        self::assertSame(1580774400, $table->getModifyDate());
        self::assertSame(609, $table->getHeaderLength());
        self::assertSame(225, $table->getRecordByteLength());
        self::assertSame(false, $table->isInTransaction());
        self::assertSame(false, $table->isEncrypted());
        self::assertSame(TableFlag::NONE, ord($table->getMdxFlag()));
        self::assertSame(0, $table->getLanguageCode());

        $columns = $table->getColumns();
        self::assertTrue(is_array($columns));
        self::assertCount(18, $columns);

        //<editor-fold desc="columns">
        $column = $columns['regn'];
        self::assertInstanceOf(ColumnInterface::class, $column);
        self::assertSame('regn', $column->getName());
        self::assertSame(FieldType::NUMERIC, $column->getType());
        self::assertSame(4, $column->getLength());
        self::assertSame(1, $column->getBytePos());
        self::assertSame(0, $column->getMemAddress());
        self::assertSame(0, $column->getColIndex());

        $column = $columns['plan'];
        self::assertInstanceOf(ColumnInterface::class, $column);
        self::assertSame('plan', $column->getName());
        self::assertSame(FieldType::CHAR, $column->getType());
        self::assertSame(1, $column->getLength());
        self::assertSame(5, $column->getBytePos());
        self::assertSame(0, $column->getMemAddress());
        self::assertSame(1, $column->getColIndex());

        $column = $columns['dt'];
        self::assertInstanceOf(ColumnInterface::class, $column);
        self::assertSame('dt', $column->getName());
        self::assertSame(FieldType::DATE, $column->getType());
        self::assertSame(8, $column->getLength());
        self::assertSame(216, $column->getBytePos());
        self::assertSame(0, $column->getMemAddress());
        self::assertSame(16, $column->getColIndex());

        unset($column, $columns);
        //</editor-fold>

        //<editor-fold desc="record">
        self::assertEmpty($table->getRecord());
        /** @var DBaseRecord $record */
        $record = $table->nextRecord();
        self::assertInstanceOf(RecordInterface::class, $record);
        $columns = $table->getColumns();
        self::assertCount(18, $columns);
        self::assertInstanceOf(ColumnInterface::class, $table->getColumn('regn'));

        $json = <<<JSON
{
    "regn": 1,
    "plan": "\u0410",
    "num_sc": "10605",
    "a_p": "1",
    "vr": 223717,
    "vv": 0,
    "vitg": 223717,
    "ora": 478743,
    "ova": 0,
    "oitga": 478743,
    "orp": 636389,
    "ovp": 0,
    "oitgp": 636389,
    "ir": 66071,
    "iv": 0,
    "iitg": 66071,
    "dt": "20190801",
    "priz": 1
}
JSON;
        self::assertJsonStringEqualsJsonString($json, json_encode($record->getData()));

        // num
        self::assertSame('10605', $record->get('num_sc'));
        self::assertSame(0.0, $record->get('vv'));
        self::assertSame(0.0, $record->vv);
        // char
        self::assertSame('А', $record->get('plan')); //cyrilic
        self::assertSame('А', $record->plan); //cyrilic
        // date
        self::assertSame('20190801', $record->get('dt'));
        self::assertSame('20190801', $record->get('dt'));
        self::assertSame('2019-08-01', $record->getDateTimeObject('dt')->format('Y-m-d'));
//        self::assertSame('Thu, 01 Aug 2019 00:00:00 +0000', $record->dt);
        $dt = new \DateTime($record->get('dt'));
        self::assertEquals('2019-08-01T00:00:00+00:00', $dt->format(DATE_W3C));
        //</editor-fold>

        $table->close();
    }

    public function testColumnNameCaseIndifference(): void
    {
        $table = new TableReader(__DIR__.'/Resources/dBase/dBaseIII_nomemo.dbf', ['encoding' => 'cp866']);
        $record = $table->nextRecord();
        self::assertSame(223717.0, $record->get('vitg'));
        self::assertSame(223717.0, $record->get('VITG'));
        self::assertSame(223717.0, $record->get('VitG'));
        self::assertSame(223717.0, $record->get('vItG'));
    }

    public function testColumnNotFound(): void
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage('Column none_column_value not found');

        $table = new TableReader(__DIR__.'/Resources/dBase/dBaseIII_nomemo.dbf', ['encoding' => 'cp866']);
        $record = $table->nextRecord();
        $record->none_column_value;
    }

    public function testReadColumns(): void
    {
        $table = new TableReader(__DIR__.'/Resources/dBase/dBaseIII_nomemo.dbf', ['encoding' => 'cp866']);
        $processedRecords = 0;
        while ($record = $table->nextRecord()) {
            $data = $record->getData();
            $processedRecords++;
        }
        self::assertSame(10, $processedRecords);
    }

    public function testDbase3(): void
    {
        $table = new TableReader(__DIR__.'/Resources/dBase/dBaseIII.dbf');

        self::assertSame(6, $table->getColumnCount());
        self::assertSame(3, $table->getRecordCount());

        self::assertSame(TableType::DBASE_III_PLUS_MEMO, $table->getVersion());
        self::assertSame(Codepage::CP1252, $table->getCodepage());
        self::assertSame(false, $table->isFoxpro());
        self::assertSame(225, $table->getHeaderLength());
        self::assertSame(70, $table->getRecordByteLength());
        self::assertSame(false, $table->isInTransaction());
        self::assertSame(false, $table->isEncrypted());
        self::assertSame(TableFlag::NONE, ord($table->getMdxFlag()));
        self::assertSame(0x03, $table->getLanguageCode());

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
        //</editor-fold>

        $this->assertRecords($table);
        $this->assertMemoImg($table);
    }

    public function testDbase4(): void
    {
        $table = new TableReader(__DIR__.'/Resources/dBase/dBaseIV.dbf');

        self::assertSame(7, $table->getColumnCount());
        self::assertSame(3, $table->getRecordCount());

        self::assertSame(TableType::DBASE_IV_MEMO, $table->getVersion());
        self::assertSame(Codepage::CP1252, $table->getCodepage());
        self::assertSame(false, $table->isFoxpro());
        self::assertSame(257, $table->getHeaderLength());
        self::assertSame(80, $table->getRecordByteLength());
        self::assertSame(false, $table->isInTransaction());
        self::assertSame(false, $table->isEncrypted());
        self::assertSame(TableFlag::NONE, ord($table->getMdxFlag()));
        self::assertSame(0x03, $table->getLanguageCode());

        $this->assertRecords($table);
        $this->assertMemoImg($table);

        $record = $table->moveTo(0);
        self::assertSame(1.2, $record->get('rate'));
        $record = $table->nextRecord();
        self::assertSame(1.23, $record->get('rate'));
        $record = $table->nextRecord();
        self::assertSame(15.16, $record->get('rate'));
    }

    public function testDbase7(): void
    {
        $table = new TableReader(__DIR__.'/Resources/dBase/dBaseVII.dbf');

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

    public function testDbase7ts(): void
    {
        $table = new TableReader(__DIR__.'/Resources/dBase/dBaseVII_ts.dbf');
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

    public function testDbase7int(): void
    {
        $table = new TableReader(__DIR__.'/Resources/dBase/dBaseVII_int.dbf');
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

    public function testDbase7double(): void
    {
        $table = new TableReader(__DIR__.'/Resources/dBase/dBaseVII_double.dbf');
        self::assertSame(1, $table->getColumnCount());
        self::assertSame(5, $table->getRecordCount());
        self::assertSame(TableType::DBASE_7_NOMEMO, $table->getVersion());

        self::assertSame(-199.99, $table->nextRecord()->get('double'));
        self::assertSame(-74.62, $table->nextRecord()->get('double'));
        self::assertSame(43.65, $table->nextRecord()->get('double'));
        self::assertSame(150.48, $table->nextRecord()->get('double'));
        self::assertSame(0.0, $table->nextRecord()->get('double'));
    }

    public function testColumnsOptions(): void
    {
        $table = new TableReader(__DIR__.'/Resources/dBase/dBaseVII.dbf', [
            'columns' => ['name', 'money'],
        ]);

        self::assertCount(2, $table->getColumns());

        $record = $table->nextRecord();
        self::assertSame('Groot', $record->get('name'));
        self::assertSame(12.1235, $record->get('money'));
    }

    /**
     * You cannot get data from unspecified columns.
     */
    public function testColumnsOptionsFail(): void
    {
        self::expectException(\Exception::class);

        $table = new TableReader(__DIR__.'/Resources/dBase/dBaseVII.dbf', [
            'columns' => ['name', 'money'],
        ]);

        $record = $table->nextRecord();
        self::assertSame('Groot', $record->get('bio'));
    }

    protected function assertMemoImg(TableReader $table)
    {
        $record = $table->moveTo(1);
        /** @var MemoObject $memoImg */
        $memoImg = $record->getMemoObject('image');
        self::assertSame($memoImg->getLength(), strlen($memoImg->getData())); //png
        $record = $table->nextRecord();
        $memoImg = $record->getMemoObject('image');
        self::assertSame($memoImg->getLength(), strlen($memoImg->getData()));
    }
}
