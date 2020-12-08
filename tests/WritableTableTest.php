<?php declare(strict_types=1);

namespace XBase\Tests;

use PHPUnit\Framework\TestCase;
use XBase\Enum\TableType;
use XBase\Record\DBaseRecord;
use XBase\Table;
use XBase\Tests\Writable\CloneTableTrait;
use XBase\WritableTable;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\WritableTable
 */
class WritableTableTest extends TestCase
{
    use CloneTableTrait;

    const FILEPATH = __DIR__.'/Resources/dBase/dBaseIII_nomemo.dbf';

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
        $expectedSize = $table->getHeaderLength() + ($table->getRecordCount() * $table->getRecordByteLength() + 1); // The last byte must be 0x1A
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
     * Issue #97
     * Write data in clone mode. Need to call `save` method.
     */
    public function testCloneMode(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

        $tableRead = new Table($copyTo, ['encoding' => 'cp866']);
        $recordsCountBeforeInsert = $tableRead->getRecordCount();
        $tableRead->close();

        $tableWrite = new WritableTable($copyTo, [
            'encoding' => 'cp866',
            'editMode' => 'clone',
        ]);
        $recordWrite = $tableWrite->appendRecord();
        $recordWrite
            ->set('regn', 2)
            ->set('plan', 'Ы');
        $tableWrite->writeRecord($recordWrite);

        // nothing has changed
        $tableRead = new Table($copyTo, ['encoding' => 'cp866']);
        self::assertSame($recordsCountBeforeInsert, $tableRead->getRecordCount());
        $tableRead->close();

        // save changes
        $tableWrite
            ->save()
            ->close();

        $tableRead = new Table($copyTo, ['encoding' => 'cp866']);
        self::assertSame($recordsCountBeforeInsert + 1, $tableRead->getRecordCount());
        $recordRead = $tableRead->pickRecord($recordsCountBeforeInsert);
        self::assertNotEmpty($recordRead);
        self::assertSame(2, $recordRead->get('regn'));
        self::assertSame('Ы', $recordRead->get('plan'));
        $tableRead->close();
    }

    /**
     * Issue #97
     * Write data in realtime mode. No need to call `save` method.
     */
    public function testRealtimeMode(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

        $tableRead = new Table($copyTo, ['encoding' => 'cp866']);
        $recordsCountBeforeInsert = $tableRead->getRecordCount();
        $tableRead->close();

        $tableWrite = new WritableTable($copyTo, [
            'encoding' => 'cp866',
            'editMode' => 'realtime',
        ]);
        $recordWrite = $tableWrite->appendRecord();
        $recordWrite
            ->set('regn', 2)
            ->set('plan', 'Ы');
        $tableWrite->writeRecord($recordWrite);

        $tableRead = new Table($copyTo, ['encoding' => 'cp866']);
        self::assertSame($recordsCountBeforeInsert + 1, $tableRead->getRecordCount());
        $recordRead = $tableRead->pickRecord($recordsCountBeforeInsert);
        self::assertNotEmpty($recordRead);
        self::assertSame(2, $recordRead->get('regn'));
        self::assertSame('Ы', $recordRead->get('plan'));

        $tableWrite->close();
        $tableRead->close();
    }
}
