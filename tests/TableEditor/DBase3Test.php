<?php declare(strict_types=1);

namespace XBase\Tests\TableEditor;

use PHPUnit\Framework\TestCase;
use XBase\Record\FoxproRecord;
use XBase\TableEditor;
use XBase\TableReader;

class DBase3Test extends TestCase
{
    use CloneTableTrait;

    const FILEPATH = __DIR__.'/../Resources/dBase/dBaseIII.dbf';

    public function testAppendRecord(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);
        $size = filesize($copyTo);
        $table = new TableEditor($copyTo);
        self::assertSame(3, $table->getRecordCount());

        /** @var FoxproRecord $record */
        $record = $table->appendRecord()
            ->set('name', 'test name')
            ->set('bio', $newBio = str_pad('', 64 - 8, '-'));
        $table
            ->writeRecord($record)
            ->save()
            ->close();

        self::assertSame($size + $table->getRecordByteLength(), filesize($copyTo));
        $table = new TableReader($copyTo);
        self::assertSame(4, $table->getRecordCount());
        $record = $table->pickRecord(3);
        self::assertSame('test name', $record->get('name'));
        self::assertSame($newBio, $record->get('bio'));
        $table->close();
    }
}
