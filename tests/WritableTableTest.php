<?php

namespace XBase\Tests;

use PHPUnit\Framework\TestCase;
use XBase\Record;
use XBase\Table;
use XBase\WritableTable;

class WritableTableTest extends TestCase
{
    const FILEPATH = __DIR__.'/Resources/dBase/dBaseIII_nomemo.dbf';

    private function duplicateFile(string $file): string
    {
        $info = pathinfo($file);
        $newName = uniqid($info['filename']);
        $copyTo = "{$info['dirname']}/$newName.{$info['extension']}";
        self::assertTrue(copy($file, $copyTo));
        return $copyTo;
    }

    public function testSet()
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);
        try {
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
        } finally {
            unlink($copyTo);
        }
    }

    /**
     * Append row to table.
     */
    public function testAppendRecord()
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);
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

            clearstatcache();
            $expectedSize = $table->headerLength + ($table->recordCount * $table->recordByteLength); // Last byte must be 0x1A
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
            self::assertSame(172800, $record->getDate('dt'));
            self::assertSame(2, $record->getNum('priz'));
            $table->close();
        } finally {
            unlink($copyTo);
        }
    }

    public function testDeleteRecord()
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);
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
        $copyTo = $this->duplicateFile(self::FILEPATH);
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

    public function testIssue78()
    {
        $fecnacim = date("m/d/Y", 86400);
        $fecingreso = date("m/d/Y", 86400 * 2);

        $copyTo = $this->duplicateFile(__DIR__.'/Resources/socios.dbf');
        try {
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
            $newRecord->venciced = \DateTime::createFromFormat("U", -777859200);
            $newRecord->nriesgo = "B";
            //save
            $table->writeRecord();
            $table->pack();
            $table->close();
            unset($newRecord);

            $table = new Table($copyTo);
            self::assertEquals(4, $table->getRecordCount());
            $record = $table->pickRecord(3);
            self::assertEquals('000000000000', $record->segsocial);
            self::assertSame('socio', $record->socio);
            self::assertSame('apellido', $record->apellido);
            self::assertSame('nombre', $record->nombre);
            self::assertSame(86400, $record->getDate('fecnacim'));
            self::assertSame($fecnacim, $record->getDateTimeObject('fecnacim')->format('m/d/Y'));
            self::assertSame($fecingreso, $record->getDateTimeObject('fecingreso')->format('m/d/Y'));
            self::assertSame('M', $record->sexo);
            self::assertSame('600', $record->apartado);
            self::assertSame('12345678', $record->telefonor);
            self::assertSame('someone@email.com', $record->email);
            self::assertSame('1945-05-09', $record->getDateTimeObject('venciced')->format('Y-m-d'));
            self::assertSame("B", $record->nriesgo);
            $table->close();
        } finally {
            unlink($copyTo);
        }
    }
}
