<?php declare(strict_types=1);

namespace XBase\Tests;

use PHPUnit\Framework\TestCase;
use XBase\Enum\TableType;
use XBase\Record\DBaseRecord;
use XBase\Record\VisualFoxproRecord;
use XBase\Table;
use XBase\WritableTable;

class WritableTableTest extends TestCase
{
    const FILEPATH = __DIR__.'/Resources/dBase/dBaseIII_nomemo.dbf';

    /** @var string[] */
    private $cloneFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->cloneFiles as $filepath) {
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
    }

    private function duplicateFile(string $file): string
    {
        $info = pathinfo($file);
        $newName = uniqid($info['filename'].'_');
        $this->cloneFiles[] = $copyTo = "{$info['dirname']}/$newName.{$info['extension']}";
        self::assertTrue(copy($file, $copyTo));

        $memoExt = ['fpt'];
        foreach ($memoExt as $ext) {
            $memoFile = "{$info['dirname']}/{$info['filename']}.$ext";
            if (file_exists($memoFile)) {
                $this->cloneFiles[] = $memoFileCopy = "{$info['dirname']}/$newName.$ext";
                self::assertTrue(copy($memoFile, $memoFileCopy));
            }
        }

        return $copyTo;
    }

    public function testSet(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

        $table = new WritableTable($copyTo, null, 'cp866');
        $table->openWrite();
        $record = $table->nextRecord();
        $record->setNum($record->getColumn('regn'), 2);
        $record->setString($record->getColumn('plan'), 'Ы');
        $table->writeRecord();
        $table->close();

        $table = new Table($copyTo, null, 'cp866');
        $record = $table->nextRecord();
        self::assertSame(2, $record->getNum('regn'));
        self::assertSame('Ы', $record->getString('plan'));
        $table->close();
    }

    /**
     * Append row to table.
     */
    public function testAppendRecord(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

        $table = new WritableTable($copyTo, null, 'cp866');

        self::assertSame(
            $table->getHeaderLength() + ($table->getRecordCount() * $table->getRecordByteLength()) + 1, // Last byte must be 0x1A
            filesize($copyTo)
        );
        self::assertSame(TableType::DBASE_III_PLUS_NOMEMO, $table->getVersion());
        self::assertEquals(10, $table->getRecordCount());

        $table->openWrite();
        $record = $table->appendRecord();
        self::assertInstanceOf(DBaseRecord::class, $record);

        $record->setNum($table->getColumn('regn'), 3);
        $record->setString($table->getColumn('plan'), 'Д');
        $record->setString($table->getColumn('num_sc'), '10101');
        $record->setString($table->getColumn('a_p'), '3');
        $record->setNum($table->getColumn('vr'), 100);
        $record->setNum($table->getColumn('vv'), 200);
        $record->setNum($table->getColumn('vitg'), 300.0201);
        $record->setDate($table->getColumn('dt'), new \DateTime('1970-01-03'));
        $record->setNum($table->getColumn('priz'), 2);
        $table->writeRecord();
        $table->pack();
        $table->close();

        clearstatcache();
        $expectedSize = $table->getHeaderLength() + ($table->getRecordCount() * $table->getRecordByteLength()); // Last byte must be 0x1A
        self::assertSame($expectedSize, filesize($copyTo));

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
        self::assertSame('19700103', $record->getDate('dt'));
        self::assertSame(2, $record->getNum('priz'));
        $table->close();
    }

    public function testDeleteRecord(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

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
    }

    public function testDeletePackRecord(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

        $table = new WritableTable($copyTo, null, 'cp866');
        self::assertEquals(10, $table->getRecordCount());
        $table->openWrite();
        $table->nextRecord(); // set pointer to first row
        $table
            ->deleteRecord()
            ->pack()
            ->close();

        $table = new Table($copyTo, null, 'cp866');
        self::assertEquals(9, $table->getRecordCount());
        $table->close();
    }

    public function testIssue78(): void
    {
        $fecnacim = date('m/d/Y', 86400);
        $fecingreso = date('m/d/Y', 86400 * 2);

        $copyTo = $this->duplicateFile(__DIR__.'/Resources/socios.dbf');

        $table = new WritableTable($copyTo);
        self::assertEquals(3, $table->getRecordCount());
        $table->openWrite();
        // fill new newRecord
        $newRecord = $table->appendRecord();
        $newRecord->segsocial = '000000000000';
        $newRecord->socio = 'socio';
        $newRecord->apellido = 'apellido';
        $newRecord->nombre = 'nombre';
        $newRecord->fecnacim = $fecnacim;
        $newRecord->fecingreso = $fecingreso;
        $newRecord->sexo = 'M';
        $newRecord->apartado = '600';
        $newRecord->telefonor = '12345678';
        $newRecord->email = 'someone@email.com';
        $newRecord->venciced = \DateTime::createFromFormat('U', '-777859200');
        $newRecord->nriesgo = 'B';
        $newRecord->salario = 5000;
        //save
        $table->writeRecord();
        $table->pack();
        $table->close();
        unset($newRecord);

        $table = new Table($copyTo);
        self::assertEquals(4, $table->getRecordCount());
        /** @var DBaseRecord $record */
        $record = $table->pickRecord(3);
        self::assertEquals('000000000000', $record->segsocial);
        self::assertSame('socio', $record->socio);
        self::assertSame('apellido', $record->apellido);
        self::assertSame('nombre', $record->nombre);
        self::assertSame('19700102', $record->getDate('fecnacim'));
        self::assertSame(86400, $record->getTimeStamp('fecnacim'));
        self::assertSame($fecnacim, $record->getDateTimeObject('fecnacim')->format('m/d/Y'));
        self::assertSame($fecingreso, $record->getDateTimeObject('fecingreso')->format('m/d/Y'));
        self::assertSame('M', $record->sexo);
        self::assertSame('600', $record->apartado);
        self::assertSame('12345678', $record->telefonor);
        self::assertSame('someone@email.com', $record->email);
        self::assertSame('1945-05-09', $record->getDateTimeObject('venciced')->format('Y-m-d'));
        self::assertSame('B', $record->nriesgo);
        self::assertSame(5000.0, $record->getNum('salario'));
        $table->close();
    }

    /**
     * Not set current record. Should not fall
     */
    public function testVisualFoxProWriteCopy(): void
    {
        $original = __DIR__.'/Resources/foxpro/vfp.dbf';
        $copyTo = $this->duplicateFile($original);

        $table = new WritableTable($copyTo);
        $table->writeRecord();
        $table->close();
        self::assertFileEquals($original, $copyTo);
    }

    /**
     * Append and delete record immediately.
     */
    public function testVisualFoxProWriteCopy2(): void
    {
        $original = __DIR__.'/Resources/foxpro/vfp.dbf';
        $copyTo = $this->duplicateFile($original);

        $table = new WritableTable($copyTo);
        $table->appendRecord();
        $table->deleteRecord();
        $table->writeRecord();
        $table->close();
        self::assertFileEquals($original, $copyTo);
    }

    /**
     * Append and delete record immediately.
     */
    public function testVisualFoxProWriteCopy3(): void
    {
        $copyTo = $this->duplicateFile(__DIR__.'/Resources/foxpro/vfp.dbf');

        $table = new WritableTable($copyTo);
        self::assertSame(TableType::VISUAL_FOXPRO_VAR, $table->getVersion());
        self::assertSame(3, $table->getRecordCount());
        $table->openWrite();
        $table->appendRecord();
        $table->writeRecord();
        $table->deleteRecord();
        $table->pack();
        $table->close();
        self::assertSame(3, $table->getRecordCount());
    }

    public function testVisualFoxProAppendRecord(): void
    {
        $base64Image = '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';
        $imageBin = base64_decode($base64Image);
        $bio = 'the one who wrote this test';

        $copyTo = $this->duplicateFile(__DIR__.'/Resources/foxpro/vfp.dbf');

        $table = new WritableTable($copyTo);
        self::assertEquals(3, $table->getRecordCount());
        self::assertEquals(TableType::VISUAL_FOXPRO_VAR, $table->getVersion());

        $table->openWrite();
        /** @var VisualFoxproRecord $record */
        $record = $table->appendRecord();
        self::assertInstanceOf(VisualFoxproRecord::class, $record);
        $record->setObject($table->getColumn('name'), 'gam6itko');
        $record->setObject($table->getColumn('birthday'), new \DateTime('1988-10-10'));
        $record->setObject($table->getColumn('is_man'), true);
        $record->setObject($table->getColumn('bio'), $bio);
        $record->setObject($table->getColumn('money'), 100.10);
        $record->setObject($table->getColumn('image'), $imageBin);
        $record->setObject($table->getColumn('rate'), 10.55);
        $record->setObject($table->getColumn('general'), 10);
        $record->setObject($table->getColumn('blob'), 'blob_string');
        $record->setObject($table->getColumn('currency'), 12.36);
        $record->setObject($table->getColumn('datetime'), new \DateTime('2020-09-10T12:34:56'));
        $record->setObject($table->getColumn('double'), 3.1415);
        $record->setObject($table->getColumn('integer'), 3);
        $record->setObject($table->getColumn('varchar'), 'varchar');
        $record->set('name_bin', 'gam6itko');
        $record->set('bio_bin', $bio);
        $record->set('varbinary', 'qwe');
        $record->set('varchar_bi', 'qwe');
        $table->writeRecord();
        $table->close();

        $table = new Table($copyTo);
        self::assertEquals(4, $table->getRecordCount());

        $record = $table->pickRecord(3);
        self::assertSame('gam6itko', $record->getObject($table->getColumn('name')));
        self::assertSame('19881010', $record->getObject($table->getColumn('birthday'))); //returns timestamp
        self::assertSame(true, $record->getObject($table->getColumn('is_man')));
        self::assertEquals($bio, $record->getObject($table->getColumn('bio')));
        self::assertSame(100.10, $record->getObject($table->getColumn('money')));
        self::assertSame($imageBin, $record->getObject($table->getColumn('image')));
        self::assertSame(10.55, $record->getObject($table->getColumn('rate')));
        self::assertSame(10, $record->getObject($table->getColumn('general')));
        self::assertSame('blob_string', $record->getObject($table->getColumn('blob')));
        self::assertSame(12.36, $record->get('currency'));
        self::assertInstanceOf(\DateTimeInterface::class, $dt = $record->get('datetime'));
        self::assertSame('2020-09-10T12:34:56+00:00', $dt->format(DATE_ATOM));
        self::assertSame('2020-09-10T12:34:56+00:00', $record->getDateTimeObject($table->getColumn('datetime')->getName())->format(DATE_ATOM));
        self::assertSame(1599696000, $dt->format('U'));
        self::assertSame(3.1415, $record->getObject($table->getColumn('double')));
        self::assertSame(3.1415, $record->get('double'));
        self::assertSame(3, $record->getObject($table->getColumn('integer')));
        self::assertSame('varchar', $record->getObject($table->getColumn('varchar')));
        self::assertSame('gam6itko', $record->getObject($table->getColumn('name_bin')));
        self::assertSame($bio, $record->getObject($table->getColumn('bio_bin')));
        self::assertSame('qwe', $record->get('varbinary'));
        self::assertSame('qwe', $record->get('varchar_bi'));
        $table->close();
    }
}
