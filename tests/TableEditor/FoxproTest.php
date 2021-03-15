<?php declare(strict_types=1);

namespace XBase\Tests\TableEditor;

use PHPUnit\Framework\TestCase;
use XBase\Record\FoxproRecord;
use XBase\TableEditor;
use XBase\TableReader;

class FoxproTest extends TestCase
{
    use CloneTableTrait;

    const FILEPATH = __DIR__.'/../Resources/foxpro/Foxpro2.dbf';

    /**
     * Method appendRecord must not increase recordCount. Only after call writeRecord it will be increased.
     */
    public function testAppendNotIncreaseRecordsCount(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);
        $table = new TableEditor($copyTo);
        self::assertSame(3, $table->getRecordCount());
        $table->appendRecord();
        self::assertSame(3, $table->getRecordCount());
        $table->writeRecord();
        self::assertSame(4, $table->getRecordCount());
        $table->save()->close();
        self::assertSame(4, $table->getRecordCount());
    }

    public function testAppendRecord(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);
        $size = filesize($copyTo);
        $table = new TableEditor($copyTo);
        self::assertSame(3, $table->getRecordCount());
        /** @var FoxproRecord $record */
        $record = $table->appendRecord();
        $record->set('name', 'test name');
        $record->set('bio', $newBio = str_pad('', 64 - 8, ' '));
        $table
            ->writeRecord()
            ->save()
            ->close();

        self::assertSame($size + $table->getRecordByteLength(), filesize($copyTo));
        $table = new TableReader($copyTo);
        self::assertSame(4, $table->getRecordCount());
        $record = $table->pickRecord(3);
        self::assertSame('test name', $record->get('name'));
        self::assertSame($newBio, $record->get('bio'));
    }

    public function testDeleteRecord(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);
        $size = filesize($copyTo);
        $table = new TableEditor($copyTo);
        self::assertSame(3, $table->getRecordCount());

        /** @var FoxproRecord $record */
        $record = $table->nextRecord();
        self::assertSame(8, $record->getMemoObject('bio')->getPointer());
        self::assertSame(32, $record->getMemoObject('image')->getPointer());

        /** @var FoxproRecord $record */
        $record = $table->nextRecord();
        self::assertNotEmpty($bioRocket = $record->get('bio'));
        self::assertSame(460, $record->getMemoObject('bio')->getPointer());
        self::assertSame(476, $record->getMemoObject('image')->getPointer());

        /** @var FoxproRecord $record */
        $record = $table->nextRecord();
        self::assertNotEmpty($bioStarLord = $record->get('bio'));
        self::assertSame(1973, $record->getMemoObject('bio')->getPointer());
        self::assertSame(1992, $record->getMemoObject('image')->getPointer());

        /** @var FoxproRecord $record */
        $record = $table->pickRecord(0);
        $table
            ->deleteRecord($record)
            ->pack()
            ->save()
            ->close();

        self::assertSame($size - $table->getRecordByteLength(), filesize($copyTo));
        $table = new TableReader($copyTo);
        self::assertSame(2, $table->getRecordCount());
        $record = $table->pickRecord(0);
        self::assertSame($bioRocket, $record->get('bio'));
        self::assertSame(9, $record->getGenuine('bio'));
        $record = $table->pickRecord(1);
        self::assertSame($bioStarLord, $record->get('bio'));
        self::assertSame(1522, $record->getGenuine('bio'));
    }
}
