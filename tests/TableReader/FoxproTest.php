<?php declare(strict_types=1);

namespace XBase\Tests\TableReader;

use XBase\Enum\Codepage;
use XBase\Enum\FieldType;
use XBase\Enum\TableFlag;
use XBase\Enum\TableType;
use XBase\TableReader;
use XBase\Tests\AbstractTestCase;

class FoxproTest extends AbstractTestCase
{
    public function testRead(): void
    {
        $table = new TableReader(__DIR__.'/../Resources/foxpro/1.dbf', ['encoding' => 'cp852']); //todo file to big need to reduce

        self::assertSame(TableType::FOXPRO_MEMO, $table->getVersion());
        self::assertSame(Codepage::CP852, $table->getCodepage());
        self::assertSame(true, $table->isFoxpro());
        self::assertSame(417, $table->getHeaderLength());
        self::assertSame(66, $table->getRecordByteLength());
        self::assertSame(false, $table->isInTransaction());
        self::assertSame(false, $table->isEncrypted());
        self::assertSame(TableFlag::NONE, ord($table->getMdxFlag()));
        self::assertSame(0x64, $table->getLanguageCode());

        self::assertSame(12, $table->getColumnCount());
//        self::assertSame(10, $table->getRecordCount());

        $columns = $table->getColumns();
        self::assertTrue(is_array($columns));
        self::assertCount(12, $columns);

        $c = $columns['poz'];
        self::assertSame(FieldType::MEMO, $c->getType());
        self::assertSame(7, $c->getColIndex());
        self::assertSame(10, $c->getLength());
        self::assertSame(0x20, $c->getMemAddress());
        self::assertSame(0x20, $c->getBytePos());
        self::assertSame(0x20, $c->getMemAddress());
        unset($c);

        //<editor-fold desc="record">
        $record = $table->nextRecord();
        self::assertSame(40777, $record->get('idc'));
        self::assertSame(1, $record->get('clv'));
        self::assertSame(57310050.0, $record->get('idn'));
        self::assertSame(51014, $record->get('pvz'));
        self::assertSame('Rozhodnutie 1/2014/ROEP Modra o schválení registra zo dňa 31.3.2014 právoplatné dňa 24.4.2014', $record->get('poz'));
        //</editor-fold>

        $table->close();
    }

    public function testFoxpro2(): void
    {
        $table = new TableReader(__DIR__.'/../Resources/foxpro/Foxpro2.dbf');

        self::assertSame(8, $table->getColumnCount());
        self::assertSame(3, $table->getRecordCount());

        self::assertSame(TableType::FOXPRO_MEMO, $table->getVersion());
        self::assertSame(Codepage::CP1252, $table->getCodepage());
        self::assertSame(true, $table->isFoxpro());
        self::assertSame(289, $table->getHeaderLength());
        self::assertSame(90, $table->getRecordByteLength());
        self::assertSame(false, $table->isInTransaction());
        self::assertSame(false, $table->isEncrypted());
        self::assertSame(TableFlag::NONE, ord($table->getMdxFlag()));
        self::assertSame(0x03, $table->getLanguageCode());

        $this->assertRecords($table);
        $this->assertMemoImg($table);

        $columns = $table->getColumns();

        //<editor-fold desc="columns">
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
        $column = $columns['rate'];
        self::assertSame(FieldType::FLOAT, $column->getType());
        self::assertSame(70, $column->getBytePos());
        self::assertSame(10, $column->getLength());
        self::assertSame(2, $column->getDecimalCount());
        $column = $columns['general'];
        self::assertSame(FieldType::GENERAL, $column->getType());
        self::assertSame(80, $column->getBytePos());
        self::assertSame(10, $column->getLength());
        //</editor-fold>

        $record = $table->moveTo(0);
        self::assertSame(1.2, $record->get('rate'));
        self::assertSame('1', $record->get('general'));

        $record = $table->nextRecord();
        self::assertSame(1.23, $record->get('rate'));
        self::assertEquals('2', $record->get('general'));

        $record = $table->nextRecord();
        self::assertSame(15.16, $record->get('rate'));
        self::assertEquals('3', $record->get('general'));
    }

    protected function assertMemoImg(TableReader $table): void
    {
        $record = $table->moveTo(1);
        $memoImg = $record->getMemoObject('image');
        self::assertSame(95714, strlen($memoImg->getData()));
        $record = $table->nextRecord();
        $memoImg = $record->getMemoObject('image');
        self::assertSame(187811, strlen($memoImg->getData()));
    }
}
