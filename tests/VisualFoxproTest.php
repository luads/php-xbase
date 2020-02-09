<?php

namespace XBase\Tests;

use PHPUnit\Framework\TestCase;
use XBase\Enum\FieldType;
use XBase\Enum\TableFlag;
use XBase\Enum\TableType;
use XBase\Table;

class VisualFoxproTest extends TestCase
{
    public function testRead()
    {
        $table = new Table(__DIR__.'/Resources/foxpro/visual_fox_pro6.dbf');

        self::assertSame(TableType::VISUAL_FOXPRO, $table->version);
        self::assertSame(true, $table->isFoxpro());
        self::assertSame(true, TableType::isVisualFoxpro($table->version));
        self::assertSame(776, $table->headerLength);
        self::assertSame(90, $table->recordByteLength);
        self::assertSame(false, $table->inTransaction);
        self::assertSame(false, $table->encrypted);
        self::assertSame(TableFlag::CDX | TableFlag::MEMO, ord($table->mdxFlag));
        self::assertSame(0x03, ord($table->languageCode));
        self::assertSame(15, $table->getColumnCount());
        self::assertSame(0, $table->getRecordCount());

        $i = 0;
        $columns = array_values($table->getColumns());
        self::assertSame(FieldType::CHAR, $columns[$i++]->getType());
        self::assertSame(FieldType::CHAR, $columns[$i++]->getType());
        self::assertSame(FieldType::MEMO, $columns[$i++]->getType());
        self::assertSame(FieldType::MEMO, $columns[$i++]->getType());
        self::assertSame(FieldType::LOGICAL, $columns[$i++]->getType());
        self::assertSame(FieldType::LOGICAL, $columns[$i++]->getType());
        self::assertSame(FieldType::LOGICAL, $columns[$i++]->getType());
        self::assertSame(FieldType::LOGICAL, $columns[$i++]->getType());
        self::assertSame(FieldType::LOGICAL, $columns[$i++]->getType());
        self::assertSame(FieldType::LOGICAL, $columns[$i++]->getType());
        self::assertSame(FieldType::LOGICAL, $columns[$i++]->getType());
        self::assertSame(FieldType::MEMO, $columns[$i++]->getType());
        self::assertSame(FieldType::MEMO, $columns[$i++]->getType());
        self::assertSame(FieldType::MEMO, $columns[$i++]->getType());
        self::assertSame(FieldType::DBFFIELD_IGNORE_0, $columns[$i++]->getType());

        $table->close();
    }
}
