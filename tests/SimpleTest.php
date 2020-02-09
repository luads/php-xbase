<?php

namespace XBase\Tests;

use PHPUnit\Framework\TestCase;
use XBase\Column;
use XBase\Enum\TableType;
use XBase\Enum\TableFlag;
use XBase\Record;
use XBase\Table;
use XBase\WritableTable;

class SimpleTest extends TestCase
{
    const FILEPATH = __DIR__.'/Resources/cbr_072019b1.dbf';

    public function testRead()
    {
        $table = new Table(self::FILEPATH, null, 'cp866');

        self::assertSame(18, $table->getColumnCount());
        self::assertSame(10, $table->getRecordCount());

        self::assertSame(3, $table->version);
        self::assertSame(TableType::DBASE_III_PLUS_NOMEMO, $table->version);
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
        self::assertInstanceOf(Column::class, $column);
        self::assertSame('regn', $column->getName());
        self::assertSame(Record::DBFFIELD_TYPE_NUMERIC, $column->getType());
        self::assertSame(4, $column->getLength());
        self::assertSame(1, $column->getBytePos());
        self::assertSame(0, $column->getColIndex());

        $column = $columns['plan'];
        self::assertInstanceOf(Column::class, $column);
        self::assertSame('plan', $column->getName());
        self::assertSame(Record::DBFFIELD_TYPE_CHAR, $column->getType());
        self::assertSame(1, $column->getLength());
        self::assertSame(5, $column->getBytePos());
        self::assertSame(1, $column->getColIndex());

        $column = $columns['dt'];
        self::assertInstanceOf(Column::class, $column);
        self::assertSame('dt', $column->getName());
        self::assertSame(Record::DBFFIELD_TYPE_DATE, $column->getType());
        self::assertSame(8, $column->getLength());
        self::assertSame(216, $column->getBytePos());
        self::assertSame(16, $column->getColIndex());

        unset($column, $columns);
        //</editor-fold>

        //<editor-fold desc="record">
        self::assertEmpty($table->getRecord());

        $record = $table->nextRecord();
        self::assertInstanceOf(Record::class, $record);
        $columns = $record->getColumns();
        self::assertCount(18, $columns);
        self::assertInstanceOf(Column::class, $record->getColumn('regn'));

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
        $table = new Table(self::FILEPATH, null, 'cp866');
        $record = $table->nextRecord();
        $record->none_column_value;
    }

    public function testReadColumns()
    {
        $table = new Table(self::FILEPATH, null, 'cp866');
        $processerResords = 0;
        while ($record = $table->nextRecord()) {
            $data = $record->getData();
            $processerResords++;
        }
        self::assertSame(10, $processerResords);
    }

}
