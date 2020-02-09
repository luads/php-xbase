<?php

namespace XBase\Tests;

use PHPUnit\Framework\TestCase;
use XBase\Record;
use XBase\Table;
use XBase\WritableTable;

class WritableTableTest extends TestCase
{
    const FILEPATH = __DIR__.'/Resources/cbr_072019b1.dbf';

    public function testSet()
    {
        $info = pathinfo(self::FILEPATH);
        $newName = uniqid($info['filename']);
        $copyTo = "{$info['dirname']}/$newName.{$info['extension']}";
        self::assertTrue(copy(self::FILEPATH, $copyTo));

        try {
            $table = new WritableTable($copyTo, null, 'cp866');
            $table->openWrite();
            $record = $table->nextRecord();
            $record->setInt($record->getColumn('regn'), 2);
            $record->setString($record->getColumn('plan'), 'Ы');
            $table->writeRecord();
            $table->close();

            $table = new Table($copyTo, null, 'cp866');
            $record = $table->nextRecord();
            self::assertSame(2, $record->getNum('regn'));
            self::assertSame('Ы', $record->getString('plan'));
            $table->close();
        } finally {
            unlink($copyTo);
        }
    }

    /**
     * Append row to table.
     */
    public function testAppendRecord()
    {
        $info = pathinfo(self::FILEPATH);
        $newName = uniqid($info['filename']);
        $copyTo = "{$info['dirname']}/$newName.{$info['extension']}";
        self::assertTrue(copy(self::FILEPATH, $copyTo));

        try {
            $table = new WritableTable($copyTo, null, 'cp866');
            $table->openWrite();
            $record = $table->appendRecord();
            self::assertInstanceOf(Record::class, $record);

            $record->setInt($record->getColumn('regn'), 3);
            $record->setString($record->getColumn('plan'), 'Д');
            $record->setString($record->getColumn('num_sc'), '10101');
            $record->setString($record->getColumn('a_p'), '3');
            $record->setInt($record->getColumn('vr'), 100);
            $record->setInt($record->getColumn('vv'), 200);
            $record->setInt($record->getColumn('vitg'), 300.0201);
            $record->setDate($record->getColumn('dt'), new \DateTime('1970-01-03'));
            $record->setInt($record->getColumn('priz'), 2);
            $table->writeRecord();
            $table->close();

            $table = new Table($copyTo, null, 'cp866');
            self::assertEquals(11, $table->getRecordCount());
            $record = $table->pickRecord(10);
            self::assertSame(3, $record->getNum('regn'));
            self::assertSame('Д', $record->getString('plan'));
            self::assertSame('10101', $record->getString('num_sc'));
            self::assertSame('3', $record->getString('a_p'));
            self::assertSame(100.0, $record->getNum('vr'));
            self::assertSame(200.0, $record->getNum('vv'));
            self::assertSame(300.0201, $record->getNum('vitg'));
            self::assertSame(172800, $record->getDate('dt'));
            self::assertSame(2, $record->getNum('priz'));
            $table->close();
        } finally {
            unlink($copyTo);
        }
    }

    public function testDeleteRecord()
    {
        $info = pathinfo(self::FILEPATH);
        $newName = uniqid($info['filename']);
        $copyTo = "{$info['dirname']}/$newName.{$info['extension']}";
        self::assertTrue(copy(self::FILEPATH, $copyTo));

        try {
            $table = new WritableTable($copyTo, null, 'cp866');
            $table->openWrite();
            $table->nextRecord(); // set pointer to first row
            $table->deleteRecord();
            $table->writeRecord();
            $table->close();

            $table = new Table($copyTo, null, 'cp866');
            self::assertEquals(10, $table->getRecordCount());
            $record = $table->pickRecord(0);
            self::assertTrue($record->isDeleted());
            $table->close();
        } finally {
            unlink($copyTo);
        }
    }

    public function testDeletePackRecord()
    {
        $info = pathinfo(self::FILEPATH);
        $newName = uniqid($info['filename']);
        $copyTo = "{$info['dirname']}/$newName.{$info['extension']}";
        self::assertTrue(copy(self::FILEPATH, $copyTo));

        try {
            $table = new WritableTable($copyTo, null, 'cp866');
            $table->openWrite();
            $table->nextRecord(); // set pointer to first row
            $table->deleteRecord();
            $table->pack();
            $table->close();

            $table = new Table($copyTo, null, 'cp866');
            self::assertEquals(9, $table->getRecordCount());
            $table->close();
        } finally {
            unlink($copyTo);
        }
    }
}
