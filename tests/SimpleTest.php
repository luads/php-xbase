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

        $table = new Table($filepath, null, 'cp866');

        self::assertSame(18, $table->getColumnCount());
        self::assertSame(10, $table->getRecordCount());

        self::assertSame(3, $table->version);
        self::assertSame(false, $table->foxpro);
        self::assertSame(1580774400, $table->modifyDate);
        self::assertSame(609, $table->headerLength);
        self::assertSame(225, $table->recordByteLength);
        self::assertSame(false, $table->inTransaction);
        self::assertSame(false, $table->encrypted);
        self::assertSame('00', bin2hex($table->mdxFlag));
        self::assertSame('00', bin2hex($table->languageCode));

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
        self::assertSame(10605, $record->getNum('num_sc'));
        self::assertSame('А', $record->getString('plan')); //cyrilic
        self::assertSame(1564617600, $record->getDate('dt'));
        self::assertSame(1564617600, $record->getObject($record->getColumn('dt')));
        $dt = new \DateTime($record->forceGetString('dt'));
        self::assertEquals('2019-08-01T00:00:00+00:00', $dt->format(DATE_W3C));
    }
}
