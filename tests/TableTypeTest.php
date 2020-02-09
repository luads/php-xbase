<?php

namespace XBase\Tests;

use PHPUnit\Framework\TestCase;
use XBase\Enum\TableType;

class TableTypeTest extends TestCase
{
    public function testUnique()
    {
        $array = [];
        $refClass = new \ReflectionClass(TableType::class);
        foreach ($refClass->getConstants() as $val) {
            self::assertFalse(in_array($val, $array));
            $array[] = $val;
        }
    }

    public function testConst()
    {
        self::assertSame(0x02, TableType::DBASE_II);
        self::assertSame(0x03, TableType::DBASE_III_PLUS_NOMEMO);
        self::assertSame(0x04, TableType::DBASE_7_NOMEMO);
        self::assertSame(0x30, TableType::VISUAL_FOXPRO);
        self::assertSame(0x31, TableType::VISUAL_FOXPRO_AI);
        self::assertSame(0x32, TableType::VISUAL_FOXPRO_VAR);
        self::assertSame(0x43, TableType::DBASE_IV_SQL_TABLE_NOMEMO);
        self::assertSame(0x63, TableType::DBASE_IV_SQL_SYSTEM_NOMEMO);
        self::assertSame(0x83, TableType::DBASE_III_PLUS_MEMO);
        self::assertSame(0x8B, TableType::DBASE_IV_MEMO_PLUS_MEMO);
        self::assertSame(0x8C, TableType::DBASE_7_MEMO);
        self::assertSame(0xCB, TableType::DBASE_IV_SQL_TABLE_MEMO);
        self::assertSame(0xE5, TableType::SMT);
        self::assertSame(0xEB, TableType::DBASE_IV_SQL_SYSTEM_MEMO);
        self::assertSame(0xF5, TableType::FOXPRO_MEMO);
        self::assertSame(0xFB, TableType::FOXBASE);
    }

    public function testIsFoxpro()
    {
        self::assertEquals(TableType::isFoxpro(1), false);
        self::assertEquals(TableType::isFoxpro(10), false);
        self::assertEquals(TableType::isFoxpro(TableType::DBASE_III_PLUS_NOMEMO), false);
        self::assertEquals(TableType::isFoxpro(TableType::VISUAL_FOXPRO), true);
        self::assertEquals(TableType::isFoxpro(TableType::VISUAL_FOXPRO_AI), true);
        self::assertEquals(TableType::isFoxpro(TableType::DBASE_III_PLUS_MEMO), true);
        self::assertEquals(TableType::isFoxpro(TableType::DBASE_IV_SQL_TABLE_MEMO), true);
        self::assertEquals(TableType::isFoxpro(TableType::FOXPRO_MEMO), true);
        self::assertEquals(TableType::isFoxpro(TableType::FOXBASE), true);
    }
}
