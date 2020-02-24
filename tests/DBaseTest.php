<?php

namespace XBase\Tests;

use XBase\Column\ColumnInterface;
use XBase\Column\DBase7Column;
use XBase\Enum\Codepage;
use XBase\Enum\FieldType;
use XBase\Enum\TableFlag;
use XBase\Enum\TableType;
use XBase\Memo\MemoObject;
use XBase\Record\RecordInterface;
use XBase\Table;

class DBaseTest extends AbstractTestCase
{
    public function testRead()
    {
        $table = new Table(__DIR__.'/Resources/dBase/dBaseIII_nomemo.dbf', null, 'cp866');

        self::assertSame(18, $table->getColumnCount());
        self::assertSame(10, $table->getRecordCount());

        self::assertSame(3, $table->version);
        self::assertSame(TableType::DBASE_III_PLUS_NOMEMO, $table->version);
        self::assertSame(Codepage::UNDEFINED, $table->getCodepage());
        self::assertSame(false, $table->foxpro);
        self::assertSame(false, $table->isFoxpro());
        self::assertSame(1580774400, $table->modifyDate);
        self::assertSame(609, $table->headerLength);
        self::assertSame(225, $table->recordByteLength);
        self::assertSame(false, $table->inTransaction);
        self::assertSame(false, $table->encrypted);
        self::assertSame(TableFlag::NONE, ord($table->mdxFlag));
        self::assertSame(0, ord($table->languageCode));

        $columns = $table->getColumns();
        self::assertIsArray($columns);
        self::assertCount(18, $columns);

        //<editor-fold desc="columns">
        $column = $columns['regn'];
        self::assertInstanceOf(ColumnInterface::class, $column);
        self::assertSame('regn', $column->getName());
        self::assertSame(FieldType::NUMERIC, $column->getType());
        self::assertSame(4, $column->getLength());
        self::assertSame(1, $column->getBytePos());
        self::assertSame(0, $column->getColIndex());

        $column = $columns['plan'];
        self::assertInstanceOf(ColumnInterface::class, $column);
        self::assertSame('plan', $column->getName());
        self::assertSame(FieldType::CHAR, $column->getType());
        self::assertSame(1, $column->getLength());
        self::assertSame(5, $column->getBytePos());
        self::assertSame(1, $column->getColIndex());

        $column = $columns['dt'];
        self::assertInstanceOf(ColumnInterface::class, $column);
        self::assertSame('dt', $column->getName());
        self::assertSame(FieldType::DATE, $column->getType());
        self::assertSame(8, $column->getLength());
        self::assertSame(216, $column->getBytePos());
        self::assertSame(16, $column->getColIndex());

        unset($column, $columns);
        //</editor-fold>

        //<editor-fold desc="record">
        self::assertEmpty($table->getRecord());

        $record = $table->nextRecord();
        self::assertInstanceOf(RecordInterface::class, $record);
        $columns = $record->getColumns();
        self::assertCount(18, $columns);
        self::assertInstanceOf(ColumnInterface::class, $record->getColumn('regn'));

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
    "dt": 1564617600,
    "priz": 1
}
JSON;
        self::assertJsonStringEqualsJsonString($json, json_encode($record->getData()));

        $json = <<<JSON
{
    "regn": "1",
    "plan": "\u0410",
    "num_sc": "10605",
    "a_p": "1",
    "vr": "223717",
    "vv": "0",
    "vitg": "223717.0000",
    "ora": "478743",
    "ova": "0",
    "oitga": "478743.0000",
    "orp": "636389",
    "ovp": "0",
    "oitgp": "636389.0000",
    "ir": "66071",
    "iv": "0",
    "iitg": "66071.0000",
    "dt": "20190801",
    "priz": "1"
}
JSON;
        self::assertJsonStringEqualsJsonString($json, json_encode($record->getChoppedData()));

        // num
        self::assertSame(10605, $record->getNum('num_sc'));
        self::assertSame(0.0, $record->getNum('vv'));
        self::assertSame(0.0, $record->vv);
        // char
        self::assertSame('А', $record->getString('plan')); //cyrilic
        self::assertSame('А', $record->plan); //cyrilic
        // date
        self::assertSame(1564617600, $record->getDate('dt'));
        self::assertSame(1564617600, $record->getObject($record->getColumn('dt')));
        self::assertSame('Thu, 01 Aug 2019 00:00:00 +0000', $record->dt);
        $dt = new \DateTime($record->forceGetString('dt'));
        self::assertEquals('2019-08-01T00:00:00+00:00', $dt->format(DATE_W3C));
        //</editor-fold>

        $table->close();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Column none_column_value not found
     */
    public function testColumnNotFound()
    {
        $table = new Table(__DIR__.'/Resources/dBase/dBaseIII_nomemo.dbf', null, 'cp866');
        $record = $table->nextRecord();
        $record->none_column_value;
    }

    public function testReadColumns()
    {
        $table = new Table(__DIR__.'/Resources/dBase/dBaseIII_nomemo.dbf', null, 'cp866');
        $processerResords = 0;
        while ($record = $table->nextRecord()) {
            $data = $record->getData();
            $processerResords++;
        }
        self::assertSame(10, $processerResords);
    }

    public function testDbase3()
    {
        $table = new Table(__DIR__.'/Resources/dBase/dBaseIII.dbf');

        self::assertSame(6, $table->getColumnCount());
        self::assertSame(3, $table->getRecordCount());

        self::assertSame(TableType::DBASE_III_PLUS_MEMO, $table->version);
        self::assertSame(Codepage::CP1252, $table->getCodepage());
        self::assertSame(false, $table->foxpro); //todo why true
        self::assertSame(false, $table->isFoxpro());
        self::assertSame(225, $table->headerLength);
        self::assertSame(70, $table->recordByteLength);
        self::assertSame(false, $table->inTransaction);
        self::assertSame(false, $table->encrypted);
        self::assertSame(TableFlag::NONE, ord($table->mdxFlag));
        self::assertSame(0x03, ord($table->languageCode));

        $this->assertRecords($table);
        $this->assertMemoImg($table);
    }

    public function testDbase4()
    {
        $table = new Table(__DIR__.'/Resources/dBase/dBaseIV.dbf');

        self::assertSame(7, $table->getColumnCount());
        self::assertSame(3, $table->getRecordCount());

        self::assertSame(TableType::DBASE_IV_MEMO, $table->version);
        self::assertSame(Codepage::CP1252, $table->getCodepage());
        self::assertSame(false, $table->foxpro);
        self::assertSame(false, $table->isFoxpro());
        self::assertSame(257, $table->headerLength);
        self::assertSame(80, $table->recordByteLength);
        self::assertSame(false, $table->inTransaction);
        self::assertSame(false, $table->encrypted);
        self::assertSame(TableFlag::NONE, ord($table->mdxFlag));
        self::assertSame(0x03, ord($table->languageCode));

        $this->assertRecords($table);
        $this->assertMemoImg($table);

        $record = $table->moveTo(0);
        self::assertSame(1.2, $record->getFloat('rate'));
        $record = $table->nextRecord();
        self::assertSame(1.23, $record->getFloat('rate'));
        $record = $table->nextRecord();
        self::assertSame(15.16, $record->getFloat('rate'));
    }

    public function testDbase7()
    {
        $table = new Table(__DIR__.'/Resources/dBase/dBaseVII.dbf');

        self::assertSame(12, $table->getColumnCount());
        self::assertSame(3, $table->getRecordCount());

        self::assertSame(TableType::DBASE_7_MEMO, $table->version);
//        self::assertSame(Codepage::CP1252, $table->getCodepage()); //todo codepage 0x26
        self::assertSame(false, $table->isFoxpro());
        self::assertSame(645, $table->headerLength);
        self::assertSame(126, $table->recordByteLength);
        self::assertSame(false, $table->inTransaction);
        self::assertSame(false, $table->encrypted);
        self::assertSame(TableFlag::NONE, ord($table->mdxFlag));

        $this->assertRecords($table);

        $record = $table->moveTo(0);
        self::assertSame(0, $record->getString('auto_inc'));
        self::assertSame(1, $record->getInt('integer'));
        self::assertSame(4.0, $record->getNum('large_int'));
        self::assertNotEmpty($record->getTimestamp('datetime'));
        self::assertSame('1800-01-01 01:01:01', $record->getDateTimeObject('datetime')->format('Y-m-d H:i:s'));

        $record = $table->nextRecord();
        self::assertSame(1, $record->getInt('auto_inc'));
        self::assertSame(2, $record->getInt('integer'));
        self::assertSame(5.0, $record->getNum('large_int'));
        self::assertSame('1970-01-01 00:00:00', $record->getDateTimeObject('datetime')->format('Y-m-d H:i:s'));
        $memoImg = $record->getMemoObject('image');
        self::assertSame(MemoObject::TYPE_IMAGE, $memoImg->getType()); //png
        self::assertSame(98026, strlen($memoImg->getData())); //png

        $record = $table->nextRecord();
        self::assertSame(2, $record->getInt('auto_inc'));
        self::assertSame(3, $record->getInt('integer'));
        self::assertSame(6.0, $record->getNum('large_int'));
        self::assertNotEmpty($record->getTimestamp('datetime'));
        self::assertSame('2020-02-20 20:20:20', $record->getDateTimeObject('datetime')->format('Y-m-d H:i:s'));
        $memoImg = $record->getMemoObject('image');
        self::assertSame(169745, strlen($memoImg->getData()));
    }

    public function testDbase7ts()
    {
        $table = new Table(__DIR__.'/Resources/dBase/dBaseVII_ts.dbf');
        self::assertSame(1, $table->getColumnCount());
        self::assertSame(15, $table->getRecordCount()); //has deleted
        self::assertSame(TableType::DBASE_7_NOMEMO, $table->version);

        /** @var DBase7Column $record */
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

    public function testDbase7int()
    {
        $table = new Table(__DIR__.'/Resources/dBase/dBaseVII_int.dbf');
        self::assertSame(1, $table->getColumnCount());
        self::assertSame(6, $table->getRecordCount());
        self::assertSame(TableType::DBASE_7_NOMEMO, $table->version);

        self::assertSame(1, $table->nextRecord()->getInt('int'));
        self::assertSame(-1, $table->nextRecord()->getInt('int'));
        self::assertSame(5000000, $table->nextRecord()->getInt('int'));
        self::assertSame(-5000000, $table->nextRecord()->getInt('int'));
        self::assertSame(2147483647, $table->nextRecord()->getInt('int'));
        self::assertSame(-2147483647, $table->nextRecord()->getInt('int'));
    }

    protected function assertMemoImg(Table $table)
    {
        $record = $table->moveTo(1);
        $memoImg = $record->getMemoObject('image');
        self::assertSame(95714, strlen($memoImg->getData())); //png
        $record = $table->nextRecord();
        $memoImg = $record->getMemoObject('image');
        self::assertSame(187811, strlen($memoImg->getData()));
    }
}
