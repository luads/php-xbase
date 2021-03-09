<?php declare(strict_types=1);

namespace XBase\Tests;

use PHPUnit\Framework\TestCase;
use XBase\Enum\TableType;
use XBase\Record\DBaseRecord;
use XBase\TableEditor;
use XBase\TableReader;
use XBase\Tests\TableEditor\CloneTableTrait;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \XBase\TableEditor
 */
class TableEditorTest extends TestCase
{
    use CloneTableTrait;

    const FILEPATH = __DIR__.'/Resources/dBase/dBaseIII_nomemo.dbf';

    public function testSet(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

        $table = new TableEditor($copyTo, ['encoding' => 'cp866']);
        $record = $table->nextRecord();
        $record->set('regn', 2);
        $record->set('plan', 'Ы');
        $table
            ->writeRecord()
            ->save()
            ->close();

        $table = new TableReader($copyTo, ['encoding' => 'cp866']);
        $record = $table->nextRecord();
        self::assertSame(2, $record->get('regn'));
        self::assertSame('Ы', $record->get('plan'));
        $table->close();
    }

    /**
     * Append row to table.
     */
    public function testAppendRecord(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

        $table = new TableEditor($copyTo, ['encoding' => 'cp866']);

        self::assertSame(
            $table->getHeaderLength() + ($table->getRecordCount() * $table->getRecordByteLength()) + 1, // Last byte must be 0x1A
            filesize($copyTo)
        );
        self::assertSame(TableType::DBASE_III_PLUS_NOMEMO, $table->getVersion());
        self::assertEquals(10, $table->getRecordCount());

        $record = $table->appendRecord();
        self::assertInstanceOf(DBaseRecord::class, $record);

        $record->set('regn', 3);
        $record->set('plan', 'Д');
        $record->set('num_sc', '10101');
        $record->set('a_p', '3');
        $record->set('vr', 100);
        $record->set('vv', 200);
        $record->set('vitg', 300.0201);
        $record->set('dt', new \DateTime('1970-01-03'));
        $record->set('priz', 2);
        $table
            ->writeRecord()
            ->pack()
            ->save()
            ->close();

        clearstatcache();
        $expectedSize = $table->getHeaderLength() + ($table->getRecordCount() * $table->getRecordByteLength() + 1); // The last byte must be 0x1A
        self::assertSame($expectedSize, filesize($copyTo));

        $table = new TableReader($copyTo, ['encoding' => 'cp866']);
        self::assertEquals(11, $table->getRecordCount());
        $record = $table->pickRecord(10);
        self::assertSame(3, $record->get('regn'));
        self::assertSame('Д', $record->get('plan'));
        self::assertSame('10101', $record->get('num_sc'));
        self::assertSame('3', $record->get('a_p'));
        self::assertSame(100.0, $record->get('vr'));
        self::assertSame(200.0, $record->get('vv'));
        self::assertSame(300.0201, $record->get('vitg'));
        self::assertSame('19700103', $record->get('dt'));
        self::assertSame(2, $record->get('priz'));
        $table->close();
    }

    public function testDeleteRecord(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

        $table = new TableEditor($copyTo, ['encoding' => 'cp866']);
        $table->nextRecord(); // set pointer to first row
        $table
            ->deleteRecord()
            ->writeRecord()
            ->save()
            ->close();

        $table = new TableReader($copyTo, ['encoding' => 'cp866']);
        self::assertEquals(10, $table->getRecordCount());
        $record = $table->pickRecord(0);
        self::assertTrue($record->isDeleted());
        $table
            ->close();
    }

    public function testDeletePackRecord(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

        $table = new TableEditor($copyTo, ['encoding' => 'cp866']);
        self::assertEquals(10, $table->getRecordCount());
        $table->nextRecord(); // set pointer to first row
        $table
            ->deleteRecord()
            ->pack()
            ->save()
            ->close();

        $table = new TableReader($copyTo, ['encoding' => 'cp866']);
        self::assertEquals(9, $table->getRecordCount());
        $table->close();
    }

    public function testDeleteUndeleteRecord(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

        $table = new TableEditor($copyTo, ['encoding' => 'cp866']);
        self::assertEquals(10, $table->getRecordCount());
        $table->nextRecord(); // set pointer to first row
        $table
            ->deleteRecord()
            ->undeleteRecord()
            ->pack()
            ->close();

        $table = new TableReader($copyTo, ['encoding' => 'cp866']);
        self::assertEquals(10, $table->getRecordCount());
        $table->close();
    }

    public function testIssue78(): void
    {
        $fecnacim = date('m/d/Y', 86400);
        $fecingreso = date('m/d/Y', 86400 * 2);

        $copyTo = $this->duplicateFile(__DIR__.'/Resources/socios.dbf');

        $table = new TableEditor($copyTo);
        self::assertEquals(3, $table->getRecordCount());
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
        $table
            ->writeRecord()
            ->pack()
            ->save()
            ->close();
        unset($newRecord);

        $table = new TableReader($copyTo);
        self::assertEquals(4, $table->getRecordCount());
        /** @var DBaseRecord $record */
        $record = $table->pickRecord(3);
        self::assertEquals('000000000000', $record->segsocial);
        self::assertSame('socio', $record->socio);
        self::assertSame('apellido', $record->apellido);
        self::assertSame('nombre', $record->nombre);
        self::assertSame('19700102', $record->get('fecnacim'));
        self::assertSame(86400, $record->getTimeStamp('fecnacim'));
        self::assertSame($fecnacim, $record->getDateTimeObject('fecnacim')->format('m/d/Y'));
        self::assertSame($fecingreso, $record->getDateTimeObject('fecingreso')->format('m/d/Y'));
        self::assertSame('M', $record->sexo);
        self::assertSame('600', $record->apartado);
        self::assertSame('12345678', $record->telefonor);
        self::assertSame('someone@email.com', $record->email);
        self::assertSame('1945-05-09', $record->getDateTimeObject('venciced')->format('Y-m-d'));
        self::assertSame('B', $record->nriesgo);
        self::assertSame(5000.0, $record->get('salario'));
        $table->close();
    }

    /**
     * Issue #97
     * Write data in clone mode. Need to call `save` method.
     */
    public function testCloneMode(): void
    {
        $copyTo = $this->duplicateFile(self::FILEPATH);

        $tableRead = new TableReader($copyTo, ['encoding' => 'cp866']);
        $recordsCountBeforeInsert = $tableRead->getRecordCount();
        $tableRead->close();

        $tableWrite = new TableEditor($copyTo, [
            'encoding' => 'cp866',
            'editMode' => 'clone',
        ]);
        $recordWrite = $tableWrite->appendRecord();
        $recordWrite
            ->set('regn', 2)
            ->set('plan', 'Ы');
        $tableWrite->writeRecord($recordWrite);

        // nothing has changed
        $tableRead = new TableReader($copyTo, ['encoding' => 'cp866']);
        self::assertSame($recordsCountBeforeInsert, $tableRead->getRecordCount());
        $tableRead->close();

        // save changes
        $tableWrite
            ->save()
            ->close();

        $tableRead = new TableReader($copyTo, ['encoding' => 'cp866']);
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

        $tableRead = new TableReader($copyTo, ['encoding' => 'cp866']);
        $recordsCountBeforeInsert = $tableRead->getRecordCount();
        $tableRead->close();

        $tableWrite = new TableEditor($copyTo, [
            'encoding' => 'cp866',
            'editMode' => 'realtime',
        ]);
        $recordWrite = $tableWrite->appendRecord();
        $recordWrite
            ->set('regn', 2)
            ->set('plan', 'Ы');
        $tableWrite->writeRecord($recordWrite);

        $tableRead = new TableReader($copyTo, ['encoding' => 'cp866']);
        self::assertSame($recordsCountBeforeInsert + 1, $tableRead->getRecordCount());
        $recordRead = $tableRead->pickRecord($recordsCountBeforeInsert);
        self::assertNotEmpty($recordRead);
        self::assertSame(2, $recordRead->get('regn'));
        self::assertSame('Ы', $recordRead->get('plan'));

        $tableWrite->close();
        $tableRead->close();
    }
}
