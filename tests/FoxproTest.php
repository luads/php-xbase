<?php

namespace XBase\Tests;

use XBase\Enum\Codepage;
use XBase\Enum\FieldType;
use XBase\Enum\TableFlag;
use XBase\Enum\TableType;
use XBase\Table;

class FoxproTest extends AbstractTestCase
{
    public function testRead()
    {
        $table = new Table(__DIR__.'/Resources/foxpro/1.dbf', null, 'cp852'); //todo file to big need to reduce

        self::assertSame(TableType::FOXPRO_MEMO, $table->version);
        self::assertSame(Codepage::CP852, $table->getCodepage());
        self::assertSame(true, $table->isFoxpro());
        self::assertSame(417, $table->headerLength);
        self::assertSame(66, $table->recordByteLength);
        self::assertSame(false, $table->inTransaction);
        self::assertSame(false, $table->encrypted);
        self::assertSame(TableFlag::NONE, ord($table->mdxFlag));
        self::assertSame(0x64, ord($table->languageCode));

        self::assertSame(12, $table->getColumnCount());
//        self::assertSame(10, $table->getRecordCount());

        $columns = $table->getColumns();
        self::assertIsArray($columns);
        self::assertCount(12, $columns);

        $c = $columns['poz'];
        self::assertSame(FieldType::MEMO, $c->getType());
        self::assertSame(7, $c->getColIndex());
        self::assertSame(10, $c->getLength());
        self::assertSame(0x20, $c->getMemAddress());
        self::assertSame(0x20, $c->getBytePos());
        unset($c);

        //<editor-fold desc="record">
        $record = $table->nextRecord();
        self::assertSame(40777, $record->getNum('idc'));
        self::assertSame(1, $record->getNum('clv'));
        self::assertSame(57310050.0, $record->getNum('idn'));
        self::assertSame(51014, $record->getNum('pvz'));
        self::assertSame('Rozhodnutie 1/2014/ROEP Modra o schválení registra zo dňa 31.3.2014 právoplatné dňa 24.4.2014', $record->getMemo('poz'));
        //</editor-fold>

        $table->close();
    }

    public function testFoxpro2()
    {
        $table = new Table(__DIR__.'/Resources/foxpro/Foxpro2.dbf');

        self::assertSame(8, $table->getColumnCount());
        self::assertSame(3, $table->getRecordCount());

        self::assertSame(TableType::FOXPRO_MEMO, $table->version);
        self::assertSame(Codepage::CP1252, $table->getCodepage());
        self::assertSame(true, $table->foxpro);
        self::assertSame(true, $table->isFoxpro());
        self::assertSame(289, $table->headerLength);
        self::assertSame(90, $table->recordByteLength);
        self::assertSame(false, $table->inTransaction);
        self::assertSame(false, $table->encrypted);
        self::assertSame(TableFlag::NONE, ord($table->mdxFlag));
        self::assertSame(0x03, ord($table->languageCode));

        $this->assertRecords($table);
        $this->assertMemoImg($table);

        $columns = $table->getColumns();

        //<editor-fold desc="columns">
        $column = $columns['rate'];
        self::assertSame(FieldType::FLOAT, $column->getType());
        self::assertSame(10, $column->getLength());
        self::assertSame(2, $column->getDecimalCount());
        $column = $columns['general'];
        self::assertSame(FieldType::GENERAL, $column->getType());
        self::assertSame(10, $column->getLength());

        $record = $table->moveTo(0);
        self::assertSame(1.2, $record->getFloat('rate'));
        self::assertSame('1', $record->getString('general'));
        $record = $table->nextRecord();
        self::assertSame(1.23, $record->getFloat('rate'));
        self::assertSame('2', $record->getString('general'));
        $record = $table->nextRecord();
        self::assertSame(15.16, $record->getFloat('rate'));
        self::assertSame('3', $record->getString('general'));
    }

    protected function assertMemoImg(Table $table)
    {
        $record = $table->moveTo(1);
        $memoImg = $record->getMemoObject('image');
        self::assertSame(95714, strlen($memoImg->getData()));
        $record = $table->nextRecord();
        $memoImg = $record->getMemoObject('image');
        self::assertSame(187811, strlen($memoImg->getData()));
    }
}
