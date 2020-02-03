<?php

namespace XBase\Tests;

use PHPUnit\Framework\TestCase;
use XBase\Column;
use XBase\Record;
use XBase\Table;

class SimpleTest extends TestCase
{
    public function testRead()
    {
        $filepath = __DIR__.'/Resources/cbr_072019b1.dbf';

        $table = new Table($filepath, null, 'CP1251');

        self::assertEquals(18, $table->getColumnCount());
        self::assertEquals(10, $table->getRecordCount());

        self::assertEquals(3, $table->version);
        self::assertEquals(false, $table->foxpro);
        self::assertEquals(1580774400, $table->modifyDate);
        self::assertEquals(609, $table->headerLength);
        self::assertEquals(225, $table->recordByteLength);
        self::assertEquals(false, $table->inTransaction);
        self::assertEquals(false, $table->encrypted);
        self::assertEquals(0x00, bin2hex($table->mdxFlag));
        self::assertEquals(0x00, bin2hex($table->languageCode));

        $columns = $table->getColumns();
        self::assertIsArray($columns);
        self::assertCount(18, $columns);

        //<editor-fold desc="columns">
        $column = $columns['regn'];
        self::assertInstanceOf(Column::class, $column);
        self::assertEquals('regn', $column->getName());
        self::assertEquals(Record::DBFFIELD_TYPE_NUMERIC, $column->getType());
        self::assertEquals(4, $column->getLength());
        self::assertEquals(1, $column->getBytePos());
        self::assertEquals(0, $column->getColIndex());

        $column = $columns['plan'];
        self::assertInstanceOf(Column::class, $column);
        self::assertEquals('plan', $column->getName());
        self::assertEquals(Record::DBFFIELD_TYPE_CHAR, $column->getType());
        self::assertEquals(1, $column->getLength());
        self::assertEquals(5, $column->getBytePos());
        self::assertEquals(1, $column->getColIndex());

        $column = $columns['dt'];
        self::assertInstanceOf(Column::class, $column);
        self::assertEquals('dt', $column->getName());
        self::assertEquals(Record::DBFFIELD_TYPE_DATE, $column->getType());
        self::assertEquals(8, $column->getLength());
        self::assertEquals(216, $column->getBytePos());
        self::assertEquals(16, $column->getColIndex());

        unset($column, $columns);
        //</editor-fold>

        $record = $table->nextRecord();
        self::assertInstanceOf(Record::class, $record);
        $columns = $record->getColumns();
        self::assertCount(18, $columns);
        self::assertInstanceOf(Column::class, $record->getColumn('regn'));

        $json = <<<JSON
{
    "regn": 1,
    "plan": "\u0402",
    "num_sc": "10605",
    "a_p": "1",
    "vr": 223717,
    "vv": false,
    "vitg": 223717,
    "ora": 478743,
    "ova": false,
    "oitga": 478743,
    "orp": 636389,
    "ovp": false,
    "oitgp": 636389,
    "ir": 66071,
    "iv": false,
    "iitg": 66071,
    "dt": 1564617600,
    "priz": 1
}
JSON;
        self::assertJsonStringEqualsJsonString($json, json_encode($record->getData()));

    }
}
