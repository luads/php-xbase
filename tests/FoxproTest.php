<?php

namespace XBase\Tests;

use PHPUnit\Framework\TestCase;
use XBase\Enum\FieldType;
use XBase\Enum\TableFlag;
use XBase\Enum\TableType;
use XBase\Table;

class FoxproTest extends TestCase
{
    public function testRead()
    {
        $table = new Table(__DIR__.'/Resources/foxpro/1.dbf', null, 'cp852'); //todo file to big need to reduce

        self::assertSame(TableType::FOXPRO_MEMO, $table->version);
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


}
