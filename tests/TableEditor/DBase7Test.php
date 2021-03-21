<?php declare(strict_types=1);

namespace XBase\Tests\TableEditor;

use PHPUnit\Framework\TestCase;
use XBase\Record\FoxproRecord;
use XBase\TableEditor;
use XBase\TableReader;

class DBase7Test extends TestCase
{
    use CloneTableTrait;

    const FILEPATH = __DIR__.'/../Resources/dBase/dBaseVII.dbf';

    public function testAppendRecord(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);
        $size = filesize($copyTo);
        $table = new TableEditor($copyTo);
        self::assertSame(3, $table->getRecordCount());
        /** @var FoxproRecord $record */
        $record = $table->appendRecord();
        $record->set('name', 'test name');
        $record->set('bio', $newBio = str_pad('', 64 - 8, '-'));
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
        self::assertSame(1, $record->getMemoObject('bio')->getPointer());
        self::assertSame(9, $record->getMemoObject('image')->getPointer());

        /** @var FoxproRecord $record */
        $record = $table->nextRecord();
        self::assertNotEmpty($bioRocket = $record->get('bio'));
        self::assertSame(4, $record->getMemoObject('bio')->getPointer());
        self::assertNotEmpty($imageRocket = $record->get('image'));
        self::assertSame(63, $record->getMemoObject('image')->getPointer());

        /** @var FoxproRecord $record */
        $record = $table->nextRecord();
        self::assertNotEmpty($bioStarLord = $record->get('bio'));
        self::assertSame(6, $record->getMemoObject('bio')->getPointer());
        self::assertNotEmpty($imageStarLord = $record->get('image'));
        self::assertSame(255, $record->getMemoObject('image')->getPointer());

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
        self::assertSame(1, $record->getGenuine('bio'));
        self::assertSame($imageRocket, $record->get('image'));
        self::assertSame(6, $record->getMemoObject('image')->getPointer());
        $record = $table->pickRecord(1);
        self::assertSame($bioStarLord, $record->get('bio'));
        self::assertSame(3, $record->getGenuine('bio'));
        self::assertSame($imageStarLord, $record->get('image'));
        self::assertSame(198, $record->getMemoObject('image')->getPointer());
    }
}
