<?php

use XBase\Enum\FieldType;
use XBase\Enum\TableType;
use XBase\Header\Column;
use XBase\Header\HeaderFactory;
use XBase\TableCreator;
use XBase\TableEditor;
use XBase\TableReader;

require_once '../vendor/autoload.php';

$columnsCount = 30;

$filepath = __DIR__.'/db.dbf';
if (file_exists($filepath)) {
    unlink($filepath);
}

createFile($filepath, $columnsCount);
generateData($filepath, $columnsCount);
collectPerformanceInfo($filepath);
collectPerformanceInfo($filepath, 100, ['name_4', 'bio_10', 'is_man_8']);

function createFile(string $filepath, int $columnsCount): void
{
    $header = HeaderFactory::create(TableType::DBASE_III_PLUS_MEMO);

    $tableCreator = new TableCreator($filepath, $header);

    for ($i = 0; $i <= $columnsCount; $i++) {
        $tableCreator
            ->addColumn(new Column([
                'name'   => "name_$i",
                'type'   => FieldType::CHAR,
                'length' => 20,
            ]))
            ->addColumn(new Column([
                'name' => "birthday_$i",
                'type' => FieldType::DATE,
            ]))
            ->addColumn(new Column([
                'name' => "is_man_$i",
                'type' => FieldType::LOGICAL,
            ]))
            ->addColumn(new Column([
                'name' => "bio_$i",
                'type' => FieldType::MEMO,
            ]))
            ->addColumn(new Column([
                'name'         => "money_$i",
                'type'         => FieldType::NUMERIC,
                'length'       => 20,
                'decimalCount' => 4,
            ]));
    }

    $tableCreator->save();
}

function generateData(string $filepath, int $columnsCount): void
{
    $table = new TableEditor($filepath);

    $date = new \DateTimeImmutable('1970-01-01');

    for ($i = 0; $i < $columnsCount; $i++) {
        $birthday = $date->add(new \DateInterval('P1D'));
        $record = $table->appendRecord()
            ->set("name_$i", "column_$i")
            ->set("birthday_$i", $birthday)
            ->set("is_man_$i", $i % 2 === 0)
            ->set("bio_$i", str_pad('', $i, '-'))
            ->set("money_$i", rand(0, $i) * 0.1);
        $table->writeRecord($record);
    }

    $table
        ->save()
        ->close();
}

function collectPerformanceInfo(string $filepath, int $iterations = 100, array $columns = [])
{
    $timetable = [];

    printf('Reading all data'.PHP_EOL);

    for ($i = 0; $i < $iterations; $i++) {
        $startTime = microtime(true);
        $tableReader = new TableReader($filepath, ['columns' => $columns]);

        while ($record = $tableReader->nextRecord()) {
            foreach ($tableReader->getColumns() as $column) {
                try {
                    $value = $record->get($column->getName());
                } catch (\Throwable $exc) {
                }
            }
        }

        $tableReader->close();

        $timetable[] = microtime(true) - $startTime;

        printf('.');
    }

    printf(PHP_EOL);
    $avg = $average = array_sum($timetable) / count($timetable);
    printf('AVG time to read all tables data: %f%s', $avg, PHP_EOL);
}
