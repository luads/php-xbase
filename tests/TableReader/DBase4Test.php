<?php declare(strict_types=1);

namespace XBase\Tests\TableReader;

use XBase\Enum\Codepage;
use XBase\Enum\FieldType;
use XBase\Enum\TableFlag;
use XBase\Enum\TableType;
use XBase\TableReader;
use XBase\Tests\AbstractTestCase;

class DBase4Test extends AbstractTestCase
{
    public function testDbase4(): void
    {
        $table = new TableReader(__DIR__.'/../Resources/dBase/dBaseIV.dbf');

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

        $column = $table->getColumn('rate');
        self::assertSame(FieldType::FLOAT, $column->getType());
        self::assertSame(10, $column->getLength());
        self::assertSame(2, $column->getDecimalCount());
        self::assertSame(70, $column->getMemAddress());
        self::assertSame(70, $column->getBytePos());

        $record = $table->moveTo(0);
        self::assertSame(1.2, $record->get('rate'));
        $record = $table->nextRecord();
        self::assertSame(1.23, $record->get('rate'));
        $record = $table->nextRecord();
        self::assertSame(15.16, $record->get('rate'));
    }
}
