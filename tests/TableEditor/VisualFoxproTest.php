<?php declare(strict_types=1);

namespace XBase\Tests\TableEditor;

use PHPUnit\Framework\TestCase;
use XBase\Column\ColumnInterface;
use XBase\Enum\TableType;
use XBase\Memo\MemoObject;
use XBase\Record\VisualFoxproRecord;
use XBase\Stream\Stream;
use XBase\TableEditor;
use XBase\TableReader;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\TableEditor
 */
class VisualFoxproTest extends TestCase
{
    use CloneTableTrait;

    const FILEPATH = __DIR__.'/../Resources/foxpro/vfp.dbf';

    public function testReSave(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);
        $table = new TableEditor($copyTo);
        $table->nextRecord();
        $table
            ->writeRecord()
            ->save()
            ->close();

        $fp = Stream::createFromFile($copyTo, 'rb+');
        $fp->seek(1);
        $fp->write(pack('C*', 0x78, 0x02, 0x11));
        $fp->seek(16);
        $fp->write(pack('C*', 0xd9, 0x25, 0xc7, 0x05));
        $fp->flush();
        $fp->close();

        self::assertFileEquals(self::FILEPATH, $copyTo);
    }

    /**
     * Not set current record. Should not fall.
     */
    public function testWriteCopy(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

        $table = new TableEditor($copyTo);
        $table->writeRecord();
        $table->close();
        self::assertFileEquals(self::FILEPATH, $copyTo);
    }

    /**
     * Append and delete record immediately.
     */
    public function testWriteCopy2(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

        $table = new TableEditor($copyTo);
        $table->appendRecord();
        $table->deleteRecord();
        $table->writeRecord();
        $table->close();
        self::assertFileEquals(self::FILEPATH, $copyTo);
    }

    /**
     * Append and delete record immediately.
     */
    public function testWriteCopy3(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

        $table = new TableEditor($copyTo);
        self::assertSame(TableType::VISUAL_FOXPRO_VAR, $table->getVersion());
        self::assertSame(3, $table->getRecordCount());
        $table->appendRecord();
        $table->writeRecord();
        $table->deleteRecord();
        $table->pack();
        $table->close();
        self::assertSame(3, $table->getRecordCount());
    }

    public function testAppendRecord(): void
    {
        $base64Image = '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';
        $imageBin = base64_decode($base64Image);
        $bio = 'the one who wrote this test';

        $copyTo = $this->duplicateFile(self::FILEPATH);

        $table = new TableEditor($copyTo);
        self::assertEquals(3, $table->getRecordCount());
        self::assertEquals(TableType::VISUAL_FOXPRO_VAR, $table->getVersion());

        /** @var VisualFoxproRecord $record */
        $record = $table->appendRecord();
        self::assertInstanceOf(VisualFoxproRecord::class, $record);
        $record->set('name', 'gam6itko');
        $record->set('birthday', new \DateTime('1988-10-10'));
        $record->set('is_man', true);
        $record->set('bio', $bio);
        $record->set('money', 100.10);
        $record->set('image', $imageBin);
        $record->set('rate', 10.55);
        $record->set('general', 10);
        $record->set('blob', 'blob_string');
        $record->set('currency', 12.36);
        $record->set('datetime', new \DateTime('2020-09-10T12:34:56'));
        $record->set('double', 3.1415);
        $record->set('integer', 3);
        $record->set('varchar', 'varchar');
        $record->set('name_bin', 'gam6itko');
        $record->set('bio_bin', $bio);
        $record->set('varbinary', 'qwe');
        $record->set('varchar_bi', 'qwe');
        $table->writeRecord();
        $table
            ->save()
            ->close();

        $table = new TableReader($copyTo);
        self::assertEquals(4, $table->getRecordCount());

        $record = $table->pickRecord(3);
        self::assertSame('gam6itko', $record->get('name'));
        self::assertSame('19881010', $record->get('birthday')); //returns timestamp
        self::assertSame(true, $record->get('is_man'));
        self::assertEquals($bio, $record->get('bio'));
        self::assertSame(100.10, $record->get('money'));
        self::assertSame($imageBin, $record->get('image'));
        self::assertSame(10.55, $record->get('rate'));
        self::assertSame(10, $record->get('general'));
        self::assertSame('blob_string', $record->get('blob'));
        self::assertSame(12.36, $record->get('currency'));
        self::assertInstanceOf(\DateTimeInterface::class, $dt = $record->get('datetime'));
        self::assertSame('2020-09-10T12:34:56+00:00', $dt->format(DATE_ATOM));
        self::assertSame('2020-09-10T12:34:56+00:00', $record->getDateTimeObject($table->getColumn('datetime')->getName())->format(DATE_ATOM));
//        self::assertSame(1599696000, $dt->format('U'));
        self::assertSame(3.1415, $record->get('double'));
        self::assertSame(3.1415, $record->get('double'));
        self::assertSame(3, $record->get('integer'));
        self::assertSame('varchar', $record->get('varchar'));
        self::assertSame('gam6itko', $record->get('name_bin'));
        self::assertSame($bio, $record->get('bio_bin'));
        self::assertSame('qwe', $record->get('varbinary'));
        self::assertSame('qwe', $record->get('varchar_bi'));
        $table->close();
    }

    public function testMemoUpdate(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);
        $table = new TableEditor($copyTo);

        $record = $table->pickRecord(0);
        self::assertNotEmpty($record->get('bio'));
        self::assertSame(5046, $record->getGenuine('bio'));
        self::assertSame(5070, $record->getGenuine('bio_bin'));

        $record = $table->pickRecord(1);
        self::assertNotEmpty($bio1 = $record->get('bio'));
        self::assertSame(5094, $record->getGenuine('bio'));
        self::assertSame(5110, $record->getGenuine('bio_bin'));

        $record = $table->pickRecord(2);
        self::assertNotEmpty($bio2 = $record->get('bio'));
        self::assertSame(5126, $record->getGenuine('bio'));
        self::assertSame(5145, $record->getGenuine('bio_bin'));

        /** @var VisualFoxproRecord $record */
        $record = $table->nextRecord();
        /** @var MemoObject $memoRecord */
        $memoRecord = $record->getMemoObject('bio');
        self::assertInstanceOf(MemoObject::class, $memoRecord);
        $bio0 = str_pad('', $memoRecord->getLength() * 2, '-');
        $record->set('bio', $bio0);
        $table
            ->writeRecord()
            ->save()
            ->close();

        $table = new TableReader($copyTo);
        $record = $table->pickRecord(0);
        self::assertSame($bio0, $record->get('bio'));
        self::assertSame(5140, $record->getGenuine('bio'));
        self::assertSame(5070 - 24, $record->getGenuine('bio_bin'));

        $record = $table->pickRecord(1);
        self::assertSame($bio1, $record->get('bio'));
        self::assertSame(5094 - 24, $record->getGenuine('bio'));
        self::assertSame(5110 - 24, $record->getGenuine('bio_bin'));

        $record = $table->pickRecord(2);
        self::assertSame($bio2, $record->get('bio'));
        self::assertSame(5126 - 24, $record->getGenuine('bio'));
        self::assertSame(5145 - 24, $record->getGenuine('bio_bin'));
    }

    public function testDeleteMemo(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);
        $table = new TableEditor($copyTo);
        self::assertSame(3, $table->getRecordCount());
        $info = pathinfo($copyTo);
        $memoFile = "{$info['dirname']}/{$info['filename']}.fpt";
        self::assertFileExists($memoFile);
        self::assertSame(330496, filesize($memoFile));
        /* @var VisualFoxproRecord $record */
        $table->nextRecord();

        $record = $table->pickRecord(1);
        $bio1 = $record->get('bio');
        self::assertSame(5094, $record->getGenuine('bio'));
        $record = $table->pickRecord(2);
        $bio2 = $record->get('bio');
        self::assertSame(5126, $record->getGenuine('bio'));

        $table
            ->deleteRecord()
            ->pack()
            ->save()
            ->close();

        $deletedBlocks = 427 + 160;
        $table = new TableReader($copyTo);
        self::assertSame(2, $table->getRecordCount());
        $record = $table->pickRecord(0);
        self::assertSame($bio1, $record->get('bio'));
        self::assertSame(5094 - $deletedBlocks, $record->getGenuine('bio'));
        $record = $table->pickRecord(1);
        self::assertSame($bio2, $record->get('bio'));
        self::assertSame(5126 - $deletedBlocks, $record->getGenuine('bio'));
        clearstatcache();
        self::assertSame(330496 - $deletedBlocks * 64, filesize($memoFile));
    }

    public function testAppendRecordSavesEndMarker(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

        $table = new TableEditor($copyTo);
        self::assertSame(3, $table->getRecordCount());
        $record = $table->appendRecord();
        $record->set('name', 'end marker');
        $table
            ->writeRecord()
            ->save()
            ->close();

        $fp = Stream::createFromFile($copyTo, 'rb+');
        $fp->seek(-1, SEEK_END);
        $endMarker = $fp->read();
        $fp->close();

        self::assertSame(0x1a, ord($endMarker));
    }

    public function testDeleteRecordSavesEndMarker(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

        $table = new TableEditor($copyTo);
        self::assertSame(3, $table->getRecordCount());
        $table->nextRecord();
        $table
            ->deleteRecord()
            ->pack()
            ->save()
            ->close();

        $table = new TableReader($copyTo);
        self::assertSame(2, $table->getRecordCount());
        $table->close();

        $fp = Stream::createFromFile($copyTo, 'rb+');
        $fp->seek(-1, SEEK_END);
        $endMarker = $fp->read();
        $fp->close();

        self::assertSame(0x1a, ord($endMarker));
    }

    public function testIssue91(): void
    {
        $copyTo = $this->duplicateFile(__DIR__.'/../Resources/foxpro/91.dbf');
        $table = new TableEditor($copyTo, ['encoding' => 'cp1250']);
        self::assertSame(0, $table->getRecordCount());
        $data = ['str1', 'str2', 'str3', 'str4'];

        foreach ($data as $value) {
            $record = $table->appendRecord();
            $record->zkratka = $value;
            $table->writeRecord();
        }

        $table
            ->save()
            ->close();

        $table = new TableReader($copyTo, ['encoding' => 'cp1250']);
        self::assertSame(4, $table->getRecordCount());

        $actual = [];
        while ($record = $table->nextRecord()) {
            $actual[] = $record->zkratka;
        }
        self::assertEquals($data, $actual);
    }

    public function testIssue115(): void
    {
        $filename = __DIR__.'/../Resources/foxpro/issue115.dbf';
        $table = new TableEditor($filename);

        $record = $table->appendRecord();
        $record->cod = 'a';
        $record->denumire = 'b';
        $record->cont_van = 'c';
        $record->tel = 'd';
        $record->bi_serie = 'e';
        $record->bi_numar = 'f';
        $record->bi_pol = 'g';
        $record->masina = 'h';
        $record->blocat = 1;

        $table
            ->writeRecord()
            ->save()
            ->close();
        unset($record, $table);

        $table = new TableReader($filename);
        self::assertSame(
            $table->getRecordByteLength(),
            array_reduce($table->getColumns(), static function (int $carry, ColumnInterface $c) {
                return $carry + $c->getLength();
            }, 1)
        );

        $record = $table->pickRecord($table->getRecordCount() - 1);
        self::assertSame($record->cod, 'a');
        self::assertSame($record->denumire, 'b');
        self::assertSame($record->cont_van, 'c');
        self::assertSame($record->tel, 'd');
        self::assertSame($record->bi_serie, 'e');
        self::assertSame($record->bi_numar, 'f');
        self::assertSame($record->bi_pol, 'g');
        self::assertSame($record->masina, 'h');
        self::assertSame($record->blocat, 1);
    }
}
