<?php declare(strict_types=1);

namespace XBase\Tests\Writable;

use PHPUnit\Framework\TestCase;
use XBase\Record\FoxproRecord;
use XBase\Table;
use XBase\WritableTable;

class DBase3TableTest extends TestCase
{
    use CloneTableTrait;

    const FILEPATH = __DIR__.'/../Resources/dBase/dBaseIII.dbf';

    public function testAppendRecord(): void
    {
        self:self::markTestIncomplete();

        $copyTo = $this->duplicateFile(self::FILEPATH);
        $size = filesize($copyTo);
        $table = new WritableTable($copyTo);
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
        $table = new Table($copyTo);
        self::assertSame(4, $table->getRecordCount());
        $record = $table->pickRecord(3);
        self::assertSame('test name', $record->get('name'));
        self::assertSame($newBio, $record->get('bio'));
    }
}